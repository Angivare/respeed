<?php

function parse_moderators($got) {
  $moderators = [];
  if (preg_match('#<span class="liste-modo-fofo">(.+)</span>#Usi', $got, $matches)) {
    $split = explode(',', $matches[1]);
    foreach ($split as $moderator_html) {
      if (preg_match('#([a-zA-Z0-9-_[\]]{3,15})#', $moderator_html, $matches)) {
        $moderators[] = $matches[1];
      }
    }
  }
  return $moderators;
}

function parse_forum($got) {
  global $jvc;
  $ret = [];

  // Nom du forum
  $ret['title'] = false;
  if (preg_match('#<h1 class="highlight">Forum (.+)</h1>#Usi', $got, $matches)) {
      $ret['title'] = $matches[1];
      $pos = strpos($ret['title'], ' - Page ');
      if ($pos !== false) {
        $ret['title'] = substr($ret['title'], 0, $pos);
      }
  }

  // Topics
  $regex = '#<tr class=".*" data-id="(?P<id>.+)">.+' .
           '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
           '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<slug>.+)\.htm" title="(?P<title>.+)".+' .
           '(?P<pseudo_span><span .+>)\s*(?P<pseudo>\S.*)\s*</span>.+' .
           '<td class="nb-reponse-topic">\s+(?P<nb_reponses>.+)\s+</td>.+' .
           '<td class="dernier-msg-topic">.+<span .+>\s+(?P<date>.+)</span>.+' .
           '.+</tr>#Usi';
  preg_match_all($regex, $got, $ret['matches']);
  $ret['matches'] = strip_matches($ret['matches']);

  $ret['has_next_page'] = strpos($got, '<div class="pagi-after">') !== false;

  preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $ret['has_parent']);

  $ret['subforums'] = false;
  $beg = strpos($got, '<ul class="liste-sous-forums">');
  $end = strpos($got, '<div class="panel panel-jv-forum">');
  $body = substr($got, $beg, $end - $beg);
  $re = '#<li class="line-ellipsis">.+' .
        '<a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>.+).htm" .+>' .
        '(?:\s+<span .+>)??' .
        '\s*?(?P<name>.+)\s*?' .
        '(?:</span>.+)??</a>.+</li>#Usi';
  if (preg_match_all($re, $body, $matches, PREG_SET_ORDER)) {
    foreach ($matches as $k => $v) {
      $matches[$k] = strip_matches($matches[$k]);
    }
    $ret['subforums'] = $matches;
  }

  $ret['moderators'] = parse_moderators($got);

  return $ret;
}

function fetch_forum($forum, $page, $slug) {
  $jvc = new Jvc();
  $db = new Db();

  if ($forum != 103 && ($cache = $db->get_forum_cache($forum, $page)) && $cache['fetched_at'] > microtime(true) - 2) {
    $ret = json_decode($cache['vars'], true);
  }
  else {
    $page_url = ($page - 1) * 25 + 1;
    $url = "/forums/0-{$forum}-0-1-0-{$page_url}-0-{$slug}.htm";
    if ($forum != 103) {
      $rep = $jvc->request($url, false);
    }
    else {
      $rep = $jvc->request($url);
    }

    $header = &$rep['header'];
    $got = &$rep['body'];

    $location = Jvc::jvf_link(Jvc::redirects($header));
    if ($location) {
      header("Location: {$location}");
      exit;
    }

    $ret = parse_forum($got);
    if ($forum != 103) {
      $db->set_forum_cache($forum, $page, json_encode($ret));
    }
  }

  return $ret;
}

function parse_topic($got) {
  global $forum, $topic, $topic_mode, $page, $slug;
  $ret = [];

  $ret['title'] = false;
  if (preg_match('#<span id="bloc-title-forum">(.+)</span>#Usi', $got, $matches)) {
      $ret['title'] = $matches[1];
  }

  $ret['forum_slug'] = 'slug';
  $ret['forum_name'] = 'Forum';
  if (preg_match('#<span><a href="/forums/0-' . $forum . '-0-1-0-1-0-(.+)\.htm">Forum (.+)</a></span>#Usi', $got, $matches)) {
      $ret['forum_slug'] = $matches[1];
      $ret['forum_name'] = $matches[2];
  }

  $ret['poll'] = false;
  if (strpos($got, '<span class="page-active">1</span>') !== false // Only get them on first page
  && preg_match('#<div class="intitule-sondage">(.+?)</div>#', $got, $matches)) {
    $ret['poll'] = $matches[1];
  }

  $ret['messages'] = [];
  $regex = '#<div class="bloc-message-forum " id="post_(?P<post>.+)".+>\s+<div class="conteneur-message">\s+' .
           '(<div class="bloc-avatar-msg">\s+<div class="back-img-msg">\s+<div>\s+<span[^>]+>\s+<img src="(?P<avatar>.+)"[^>]+>\s+</span>\s+</div>\s+</div>\s+</div>\s+)?' .
           '<div class="inner-head-content">.+(<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)"|<div class="bloc-pseudo-msg").+' .
           '>\s+(?P<pseudo>.+)\s+<.+' .
           '<div class="bloc-date-msg">\s+(<span[^>]+>)?(?P<date>[0-9].+)</div>.+' .
           '<div class="txt-msg  text-enrichi-forum ">(?P<message>.*)</div>' .
           '</div>\s+</div>\s+</div>\s+</div>#Usi';
  preg_match_all($regex, $got, $matches);
  for ($i = 0; $i < count($matches[0]); $i++) {
    $dateRaw = strip_tags(trim($matches['date'][$i]));
    $avatar = $avatarBig = false;
    if ($matches['avatar'][$i] && strrpos($matches['avatar'][$i], '/default.jpg') === false) {
      $avatar = $matches['avatar'][$i];
      $avatarBig = str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $matches['avatar'][$i]);
    }
    $id = (int)$matches['post'][$i];
    $content = adapt_html($matches['message'][$i], $dateRaw, $id);
    $ret['messages'][] = [
      'pseudo' => htmlspecialchars(trim($matches['pseudo'][$i])),
      'avatar' => $avatar,
      'avatarBig' => $avatarBig,
      'dateRaw' => $dateRaw,
      'content' => $content,
      'contentChecksum' => md5($content),
      'id' => $id,
      'status' => $matches['status'][$i],
    ];
  }

  $ret['last_page'] = 1;
  if (preg_match_all('#<span><a href="/forums/[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9a-z-]+\.htm" class="lien-jv">([0-9]+)</a></span>#Usi', $got, $matches2)) {
    $ret['last_page'] = array_pop($matches2[1]);
  }
  if ($page > $ret['last_page']) { // Si on est sur la dernière page elle ne sera pas capturée par la regex du dessus
    $ret['last_page'] = $page;
  }

  preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $ret['has_parent']);
  $ret['has_parent'] = strip_matches($ret['has_parent']);

  preg_match('#var id_topic = (?P<topic_id_new>[0-9]+);\s+// ]]>\s+</script>#Usi', $got, $matches_id);
  if ($matches_id) {
    $ret['topic_id_new'] = $matches_id['topic_id_new'];
  }

  $ret['locked'] = preg_match('`<span style="color: #FF6600;">(?P<lock_rationale>.+)</span></b>`Usi', $got, $matches);
  if ($ret['locked']) {
    $ret['lock_rationale'] = $matches['lock_rationale'];
  }

  $ret['moderators'] = parse_moderators($got);

  return $ret;
}

function fetch_topic($topic_id_array, $page, $slug, $forum, $allow_old_cache = false) {
  extract($topic_id_array);

  $url = "/forums/{$topic_mode}-{$forum}-{$topic_id_old_or_new}-{$page}-0-1-0-{$slug}.htm";

  $jvc = new Jvc();
  $db = new Db();

  if ($forum != 103 && ($cache = $db->get_topic_cache($topic_id_old_or_new, $page, $topic_mode, $forum)) && ($allow_old_cache || $cache['fetched_at'] > microtime(true) - 2)) {
    $ret = json_decode($cache['vars'], true);
  }
  else {
    if (time() - $jvc->tokens_last_update() >= 3600 / 2) {
      $rep = $jvc->request($url);
      $jvc->tokens_refresh($rep['body']);
    }
    else {
      if ($forum != 103) {
        $rep = $jvc->request($url, false);
      }
      else {
        $rep = $jvc->request($url);
      }
    }

    $header = &$rep['header'];
    $got = &$rep['body'];

    $location = Jvc::jvf_link(Jvc::redirects($header));
    if ($location) {
      header("Location: {$location}");
      exit;
    }

    $ret = parse_topic($got);
    if ($forum != 103) {
      $db->set_topic_cache($topic_id_old_or_new, $page, $topic_mode, $forum, json_encode($ret));
    }
  }
  return $ret;
}

function parse_profile($body) {
  $ret = [];

  if (preg_match('#<img alt="Avatar de (?P<pseudo>[^"]+)" src="(?P<avatar>[^"]+)">#Usi', $body, $matches)) {
    $ret['pseudo'] = $matches['pseudo'];
    if ($matches['avatar'] != '//image.jeuxvideo.com/avatar-md/default.jpg') {
      $ret['avatar'] = str_replace(['/avatar-md/', '/avatars-md/'], ['/avatar/', '/avatars/'], $matches['avatar']);
    }
  }

  if (preg_match('#<div class="info-lib">Messages Forums :</div><div class="info-value">(?P<messages>[0-9.]+) messages?</div>#Usi', $body, $matches)) {
    $ret['messages'] = str_replace('.', '', $matches['messages']);
  }

  if (preg_match('#<div class="info-lib">Membre depuis :</div><div class="info-value">\S+ (?P<month>\S+) (?P<year>[0-9]+) \((?P<days>[0-9.]+) jours?\)</div>#Usi', $body, $matches)) {
    $ret['month'] = $matches['month'];
    $ret['year'] = $matches['year'];
    $ret['days'] = str_replace('.', '', $matches['days']);
  }

  if (isset($ret['messages'], $ret['days'])) {
    $ret['ratio'] = $ret['messages'] / ($ret['days'] + 1);
  }

  if (preg_match('#<div class="bloc-description-desc txt-enrichi-desc-profil">(?P<description>.+)</div>\s{32}#Usi', $body, $matches)) {
    $description = trim($matches['description']);
    if ($description) {
      $ret['description'] = adapt_html($description);
    }
  }

  if (preg_match('#<p>Signature dans les forums :</p>\s+<div>(?P<signature>.+)</div>\s+</div>\s{20}#Usi', $body, $matches)) {
    $ret['signature'] = adapt_html($matches['signature']);
  }

  if (preg_match('#<div class="alert-row"> Le pseudo est banni. </div>#Usi', $body)) {
    $ret['banned'] = true;
  }

  return $ret;
}

function fetch_profile($pseudo) {
  $pseudo = strtolower($pseudo);
  $jvc = new Jvc();
  $rep = $jvc->request('/profil/' . $pseudo . '?mode=infos', false);
  return parse_profile($rep['body']);
}

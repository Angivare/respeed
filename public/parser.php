<?php

function sub_forums($body) {
  $beg = strpos($body, '<ul class="liste-sous-forums">');
  $end = strpos($body, '<div class="panel panel-jv-forum">');
  $body = substr($body, $beg, $end - $beg);
  $re = '#<li class="line-ellipsis">.+' .
        '<a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>.+).htm" .+>' .
        '(?:\s+<span .+>)??' .
        '\s*?(?P<human>.+)\s*?' .
        '(?:</span>.+)??</a>.+</li>#Usi';
  preg_match_all($re, $body, $matches, PREG_SET_ORDER);
  foreach ($matches as $k => $v) {
    $matches[$k] = strip_matches($matches[$k]);
  }
  return $matches;
}

function parse_forum($got) {
  global $forum, $page, $slug;
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
  $regex = '#<tr class=".*" data-id=".+">.+' .
           '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
           '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<slug>.+)\.htm" title="(?P<title>.+)".+' .
           '(?P<pseudo_span><span .+>)\s*(?P<pseudo>\S.*)\s*</span>.+' .
           '<td class="nb-reponse-topic">\s+(?P<nb_reponses>.+)\s+</td>.+' .
           '<td class="dernier-msg-topic">.+<span .+>\s+(?P<date>.+)</span>.+' .
           '.+</tr>#Usi';
  preg_match_all($regex, $got, $ret['matches']);
  $ret['matches'] = strip_matches($ret['matches']);

  $ret['has_next_page'] = strpos($got, '<div class="pagi-after"></div>') === false;

  preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $ret['has_parent']);
  $ret['sous_forums'] = sub_forums($got);

  return $ret;
}

function fetch_forum($forum, $page, $slug) {
  $path = "/$forum-$slug/$page";

  $jvc = new Jvc();
  $db = new Db();

  $cache = $db->get_forum_cache($forum, $page);

  if ($cache && $cache['fetched_at'] > microtime(true) - 2) {
    $ret = json_decode($cache['vars'], true);
  }
  else {
    $page_url = ($page - 1) * 25 + 1;
    $url = "http://www.jeuxvideo.com/forums/0-{$forum}-0-1-0-{$page_url}-0-{$slug}.htm";
    $rep = $jvc->get($url, null, false, false);

    $header = &$rep['header'];
    $got = &$rep['body'];

    $location = Jvc::toJvf(Jvc::redirects($header));
    if ($location) {
      header("Location: {$location}");
      exit;
    }

    $ret = parse_forum($got);
    $db->set_forum_cache($forum, $page, json_encode($ret));
  }

  return $ret;
}

function parse_topic($got) {
  global $forum, $topic, $topic_mode, $page, $slug;
  $ret = [];

  // Titre du topic
  $ret['title'] = false;
  if (preg_match('#<span id="bloc-title-forum">(.+)</span>#Usi', $got, $matches)) {
      $ret['title'] = $matches[1];
  }

  // Slug et nom du forum
  $ret['forum_slug'] = 'slug';
  $ret['forum_name'] = 'Forum';
  if (preg_match('#<span><a href="/forums/0-' . $forum . '-0-1-0-1-0-(.+)\.htm">Forum (.+)</a></span>#Usi', $got, $matches)) {
      $ret['forum_slug'] = $matches[1];
      $ret['forum_name'] = $matches[2];
  }

  // Sondages
  $ret['poll'] = false;
  if (preg_match('#<div class="intitule-sondage">(.+?)</div>#', $got, $matches)) {
    $ret['poll'] = ['question' => $matches[1]];
    $regex = '#<tr>.+' .
             '<td class="result-pourcent">.+' .
             '<div class="pourcent">(?P<pourcent>[0-9]{1,3})\s*%</div>.+' .
             '</td>.+<td class="reponse">(?P<human>.+)</td>.+' .
             '</tr>#Usi';
    $ret['poll']['answers'] = [];
    if (preg_match_all($regex, $got, $matches)) {
      for ($i = 0; $i < count($matches[0]); $i++) {
        $ret['poll']['answers'][] = [
          'value' => $matches['pourcent'][$i],
          'human' => $matches['human'][$i],
        ];
      }
    }
    $ret['poll']['ans_count'] = 0;
    if (preg_match('#<div class="pied-result">.+([0-9]+)\s+vote.+?</div>#Usi', $got, $matches)) {
      $ret['poll']['ans_count'] = $matches[1];
    }
    $ret['poll']['closed'] = false;
    if (preg_match('#<div class="bloc-options-sondage">.+<span>Sondage fermé</span>.+</div>#Usi', $got)) {
      $ret['poll']['closed'] = true;
    }
  }

  // Messages
  $regex = '#<div class="bloc-message-forum " id="post_(?P<post>.+)".+>\s+<div class="conteneur-message">\s+' .
           '(<div class="bloc-avatar-msg">\s+<div class="back-img-msg">\s+<div>\s+<span[^>]+>\s+<img src="(?P<avatar>.+)"[^>]+>\s+</span>\s+</div>\s+</div>\s+</div>\s+)?' .
           '<div class="inner-head-content">.+(<span class="JvCare [0-9A-F]+ bloc-pseudo-msg text-(?P<status>.+)"|<div class="bloc-pseudo-msg").+' .
           '>\s+(?P<pseudo>.+)\s+<.+' .
           '<div class="bloc-date-msg">\s+(<span[^>]+>)?(?P<date>[0-9].+)</div>.+' .
           '<div class="txt-msg  text-enrichi-forum ">(?P<message>.*)</div>' .
           '</div>\s+</div>\s+</div>\s+</div>#Usi';
  preg_match_all($regex, $got, $matches);

  $ret['messages'] = [];
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
      'pos' => $i,
      'pseudo' => htmlspecialchars(trim($matches['pseudo'][$i])),
      'avatar' => $avatar,
      'avatarBig' => $avatarBig,
      'dateRaw' => $dateRaw,
      'date' => relative_date_messages($dateRaw),
      'content' => $content,
      'contentMd5' => md5($content),
      'id' => $id,
      'status' => $matches['status'][$i],
    ];
  }

  // Pagination
  $ret['last_page'] = 1;
  if (preg_match_all('#<span><a href="/forums/[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9a-z-]+\.htm" class="lien-jv">([0-9]+)</a></span>#Usi', $got, $matches2)) {
    $ret['last_page'] = array_pop($matches2[1]);
  }
  if ($page > $ret['last_page']) { // Si on est sur la dernière page elle ne sera pas capturée par la regex du dessus
    $ret['last_page'] = $page;
  }

  preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $ret['has_parent']);
  $ret['has_parent'] = strip_matches($ret['has_parent']);

  preg_match('#var id_topic = (?P<id_topic>[0-9]+);\s+// ]]>\s+</script>#Usi', $got, $matches_id);
  if ($matches_id) {
    $ret['topicNew'] = $matches_id['id_topic'];
  }

  $ret['locked'] = preg_match('`<span style="color: #FF6600;">(?P<raison>.+)</span></b>`Usi', $got, $matches) ? true : false;
  if ($ret['locked']) {
    $ret['lock_raison'] = $matches['raison'];
  }

  return $ret;
}

function fetch_topic($topic, $page, $slug, $forum) {
  $path = "/$forum/$topic-$slug/$page";

  $topic_mode = $topic[0] === '0' ? 1 : 42;
  $topic = (int) $topic;
  $url = "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm";

  $jvc = new Jvc();
  $db = new Db();

  $cache = $db->get_topic_cache($topic, $page, $topic_mode, $forum);

  if ($cache && $cache['fetched_at'] > microtime(true) - 2) {
    $ret = json_decode($cache['vars'], true);
  }
  else {
    if ($jvc->is_connected() && time() - $jvc->tokens_last_update() >= 3600 / 2) {
      $rep = $jvc->get($url);
      $jvc->tokens_refresh($rep['body']);
    }
    else {
      $rep = $jvc->get($url, null, false, false);
    }

    $header = &$rep['header'];
    $got = &$rep['body'];

    $location = Jvc::toJvf(Jvc::redirects($header));
    if ($location) {
      header("Location: {$location}");
      exit;
    }

    $ret = parse_topic($got);
    $db->set_topic_cache($topic, $page, $topic_mode, $forum, json_encode($ret));
  }

  $ret['topic_mode'] = $topic_mode;
  $ret['topic'] = $topic;
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

  if (preg_match('#<div id="content" style="background: url\(\'(?P<couverture>.+)\'#Usi', $body, $matches)) {
    $ret['couverture'] = $matches['couverture'];
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
  $rep = $jvc->get('http://www.jeuxvideo.com/profil/' . $pseudo, 'mode=infos', false, false);
  return parse_profile($rep['body']);
}

<?php

function sub_forums($body) {
  $beg = strpos($body, '<ul class="liste-sous-forums">');
  $end = strpos($body, '<div class="panel panel-jv-forum">');
  $body = substr($body, $beg, $end-$beg);
  $re = '#<li class="line-ellipsis">.+' .
        '<a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>.+).htm" .+>' .
        '(?:\s+<span .+>)??' .
        '\s*?(?P<human>.+)\s*?' .
        '(?:</span>.+)??</a>.+</li>#Usi';
  preg_match_all($re, $body, $matches, PREG_SET_ORDER);
  foreach($matches as $k => $v)
    $matches[$k] = strip_matches($matches[$k]);
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

  $ret['connected'] = connected($got);
  $ret['moderators'] = moderators($got);
  $ret['hot_topics'] = hot_topics($got);

  // Topics
  $regex = '#<tr class=".*" data-id=".+">.+' .
           '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
           '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<slug>.+)\.htm" title="(?P<title>.+)">.+' .
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
  $jvc = new Jvc();
  $db = new Db();

  $cache = delay(function() use (&$db, &$forum, &$page) {
    return $db->get_forum_cache($forum, $page);
  }, $t_db);

  if($cache && $cache['fetched_at'] > microtime(TRUE) - 2) {
    $t_req = 0;
    $ret = json_decode($cache['vars'], TRUE);
  } else {
    $page_url = ($page - 1) * 25 + 1;
    $url = "http://www.jeuxvideo.com/forums/0-{$forum}-0-1-0-{$page_url}-0-{$slug}.htm";
    $rep = delay(function() use(&$jvc, &$url) {
      return $jvc->get($url, NULL, FALSE, FALSE);
    }, $t_req);

    $header = &$rep['header'];
    $got = &$rep['body'];

    $location = JVc::redirects($header);
    if($location) {
      preg_match('#/forums/0-(?P<forum>.+)-0-1-0-1-0-(?P<slug>.+).htm#U', $location, $matches);
      header("Location: /{$matches['forum']}-{$matches['slug']}");
      exit;
    }

    $ret = parse_forum($got);
    $db->set_forum_cache($forum, $page, json_encode($ret));
  }
  $ret['t_db'] = $t_db;
  $ret['t_req'] = $t_req;
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

  $ret['connected'] = connected($got);
  $ret['moderators'] = moderators($got);
  $ret['hot_topics'] = hot_topics($got);

  //Sondages
  $ret['question'] = '';
  if(preg_match('#<div class="intitule-sondage">(.+?)</div>#', $got, $matches))
    $ret['question'] = $matches[1];
  $regex = '#<tr>.+' .
           '<td class="result-pourcent">.+' .
           '<div class="pourcent">(?P<pourcent>[0-9]{1,3})\s*%</div>.+' .
           '</td>.+<td class="reponse">(?P<human>.+)</td>.+' .
           '</tr>#Usi';
  $ret['answers'] = [];
  if(preg_match_all($regex, $got, $matches))
    for($i = 0; $i < count($matches[0]); $i++)
      $ret['answers'][] = ['pourcent' => $matches['pourcent'][$i], 'human' => $matches['human'][$i] ];

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
      $avatar = str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars-md/', '/avatar-md/'], $matches['avatar'][$i]);
      $avatarBig = str_replace(['/avatars-sm/', '/avatar-sm/'], ['/avatars/', '/avatar/'], $matches['avatar'][$i]);
    }
    $content = adapt_html($matches['message'][$i], $dateRaw);
    $ret['messages'][] = [
      'pos' => $i,
      'pseudo' => htmlspecialchars(trim($matches['pseudo'][$i])),
      'avatar' => $avatar,
      'avatarBig' => $avatarBig,
      'dateRaw' => $dateRaw,
      'date' => relative_date_messages($dateRaw),
      'content' => $content,
      'contentMd5' => md5($content),
      'id' => (int)$matches['post'][$i],
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
  $ret['sous_forums'] = sub_forums($got);

  preg_match('#var id_topic = (?P<id_topic>[0-9]+);\s+// ]]>\s+</script>#Usi', $got, $matches_id);
  if ($matches_id) {
    $ret['topicNew'] = $matches_id['id_topic'];
  }

  $ret['locked'] = preg_match('`<span style="color: #FF6600;">(?P<raison>.+)</span></b>`Usi', $got, $matches) ? TRUE : FALSE;
  if($ret['locked']) $ret['lock_raison'] = $matches['raison'];

  return $ret;
}

function fetch_topic($topic, $page, $slug, $forum) {
  $topic_mode = $topic[0] === '0' ? 1 : 42;
  $topic = (int) $topic;
  $url = "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm";

  $jvc = new Jvc();
  $db = new Db();

  $cache = delay(function() use (&$db, &$topic, &$page, &$topic_mode, &$forum) {
    return $db->get_topic_cache($topic, $page, $topic_mode, $forum);
  }, $t_db);

  if($cache && $cache['fetched_at'] > microtime(TRUE) - 2) {
    $t_req = 0;
    $ret = json_decode($cache['vars'], TRUE);
  } else {
      if($jvc->is_connected() && time() - $jvc->tokens_last_update() >= 3600/2) {
        $rep = delay( function() use (&$jvc, &$url) {
          return $jvc->get($url);
        }, $t_req);
        $jvc->tokens_refresh($rep['body']);
      } else {
        $rep = delay( function() use (&$jvc, &$url) {
          return $jvc->get($url, NULL, FALSE, FALSE);
        }, $t_req);
      }

    $header = &$rep['header'];
    $got = &$rep['body'];

    $location = Jvc::redirects($header);
    if($location) {
      preg_match('#/forums/(?P<topic_mode>.+)-(?P<forum>.+)-(?P<topic>.+)-(?P<page>.+)-0-1-0-(?P<slug>.+).htm#U', $location, $matches);
      if($matches['topic_mode'] == '1') $matches['topic'] = '0' . $matches['topic'];
      $location = "/{$matches['forum']}/{$matches['topic']}-{$matches['slug']}";
      if ($matches['page'] > 1) {
        $location .= "/{$matches['page']}";
      }
      header("Location: {$location}");
      exit;
    }

    $ret = parse_topic($got);
    $db->set_topic_cache($topic, $page, $topic_mode, $forum, json_encode($ret));
  }
  $ret['topic_mode'] = $topic_mode;
  $ret['topic'] = $topic;
  $ret['t_db'] = $t_db;
  $ret['t_req'] = $t_req;
  return $ret;
}

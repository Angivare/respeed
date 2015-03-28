<?php

function parse_forum($got) {
  global $forum, $page, $slug;
  $ret = [];

  // Nom du forum
  $ret['title'] = 'Communauté';
  if (preg_match('#<h1 class="highlight">Forum (.+)</h1>#Usi', $got, $matches)) {
      $ret['title'] = $matches[1];
  }

  // Topics
  $regex = '#<tr class=".*" data-id=".+">.+' .
           '<img src="/img/forums/topic-(?P<label>.+)\.png".+' .
           '<a href="/forums/(?P<mode>.+)-.+-(?P<topic>.+)-1-0-1-0-(?P<slug>.+)\.htm" title="(?P<title>.+)">.+' .
           '(?P<pseudo_span><span .+>)\s*(?P<pseudo>\S.*)\s*</span>.+' .
           '<td class="nb-reponse-topic">\s+(?P<nb_reponses>.+)\s+</td>.+' .
           '<td class="dernier-msg-topic">.+<span .+>\s+(?P<date>.+)</span>.+' .
           '.+</tr>#Usi';
  preg_match_all($regex, $got, $ret['matches']);
  strip_matches($ret['matches']);

  $ret['has_next_page'] = strpos($got, '<div class="pagi-after"></div>') === false;

  preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $ret['has_parent']);
  $ret['sous_forums'] = Jvc::sub_forums($got);

  return $ret;
}

function fetch_forum($forum, $page, $slug) {
  $jvc = new Jvc();
  $db = new Db();
  $cache = $db->get_forum_cache($forum, $page);

  if($cache && $cache['fetched_at'] > microtime(TRUE) - 2) {
    $ret = json_decode($cache['vars'], TRUE);
  } else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $page_url = ($page - 1) * 25 + 1;
    $url = "http://www.jeuxvideo.com/forums/0-{$forum}-0-1-0-{$page_url}-0-{$slug}.htm";
    curl_setopt($ch, CURLOPT_URL, $url);
    $got = curl_exec($ch);

    $header = substr($got, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
    $location = JVc::redirects($header);
    if($location) {
      preg_match('#/forums/0-(?P<forum>.+)-0-1-0-1-0-(?P<slug>.+).htm#U', $location, $matches);
      header("Location: /{$matches['forum']}-{$matches['slug']}");
      exit;
    }

    $fetched_vars = parse_forum($got);
    $ret = $fetched_vars;

    //Caching
    $cache = $db->get_forum_cache($forum, $page);
    if($cache && $cache['fetched_at'] > time() - 60*5) {
      function comp_date($a, $b) {
        return date_topic_list_to_timestamp($a) > date_topic_list_to_timestamp($b);
      }
      $vars = json_decode($cache['vars'], TRUE);
      $cache_max = array_max($vars['matches']['date'], 'comp_date');
      $got_max = array_max($fetched_vars['matches']['date'], 'comp_date');
      if(comp_date($cache_max, $got_max))
        $ret = $vars;
      else
        $db->set_forum_cache($forum, $page, json_encode($fetched_vars));
    } else
      $db->set_forum_cache($forum, $page, json_encode($fetched_vars));
  }
  return $ret;
}

function parse_topic($got) {
  global $forum, $topic, $topic_mode, $page, $slug;
  $ret = [];

  // Titre du topic
  $ret['title'] = 'Topic';
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
  preg_match_all($regex, $got, $ret['matches']);
  strip_matches($ret['matches']);

  // Pagination
  $ret['last_page'] = 1;
  if (preg_match_all('#<span><a href="/forums/[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9]+-[0-9a-z-]+\.htm" class="lien-jv">([0-9]+)</a></span>#Usi', $got, $matches2)) {
    $ret['last_page'] = array_pop($matches2[1]);
  }
  if ($page > $ret['last_page']) { // Si on est sur la dernière page elle ne sera pas capturée par la regex du dessus
    $ret['last_page'] = $page;
  }

  $ret['pages'] = [];
  for ($i = $page; $i < 7; $i++) {
    $ret['pages'][] = ' ';
  }
  if ($page != 1) {
    $ret['pages'][] = 1;
    for ($i = $page - 5; $i < $page; $i++) {
      if ($i > 1) {
        $ret['pages'][] = $i;
      }
    }
  }
  $ret['pages'][] = $page;
  if ($page != $ret['last_page']) {
    for ($i = $page + 1; $i <= $page + 5; $i++) {
      if ($i < $ret['last_page']) {
        $ret['pages'][] = $i;
      }
    }
    $ret['pages'][] = $ret['last_page'];
  }
  for ($i = $ret['last_page'] - $page; $i < $ret['last_page'] - $ret['last_page'] + 6; $i++) {
    $ret['pages'][] = ' ';
  }

  preg_match('#<span><a href="/forums/0-(?P<id>[0-9]+)-0-1-0-1-0-(?P<slug>[a-z0-9-]+).htm">Forum principal (?P<human>.+)</a></span>#Usi', $got, $ret['has_parent']);
  strip_matches($ret['has_parent']);
  $ret['sous_forums'] = Jvc::sub_forums($got);

  preg_match('#var id_topic = (?P<id_topic>[0-9]+);\s+// ]]>\s+</script>#Usi', $got, $matches_id);
  if ($matches_id) {
    $ret['topicNew'] = $matches_id['id_topic'];
  }

  $ret['locked'] = preg_match('`<span style="color: #FF6600;">(?P<raison>.+)</span></b>`Usi', $got, $matches) ? TRUE : FALSE;

  return $ret;
}

function fetch_topic($topic, $page, $slug, $forum) {
  $topic_mode = $_GET['topic'][0] === '0' ? 1 : 42;
  $url = "http://www.jeuxvideo.com/forums/{$topic_mode}-{$forum}-{$topic}-{$page}-0-1-0-{$slug}.htm";

  $jvc = new Jvc();
  $db = new Db();
  $cache = $db->get_topic_cache($topic, $page, $topic_mode, $forum);

  if($cache && $cache['fetched_at'] > microtime(TRUE) - 2) {
    $ret = json_decode($cache['vars'], TRUE);
  } else {
    if(time() - $jvc->tokens_last_update() >= 3600/2) {
      $got = $jvc->get($url);
      $jvc->refresh_tokens($got['body']);
      $header = $got['header'];
      $got = $got['header'] . $got['body'];
    } else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_URL, $url);
      $got = curl_exec($ch);
      $header = substr($got, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
    }

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

    $fetched_vars = parse_topic($got);
    $ret = $fetched_vars;

    //Caching
    if($cache && $cache['fetched_at'] > time() - 60*5) {
      function comp_date($a, $b) {
        return date_messages_to_timestamp(strip_tags(trim($a))) > date_messages_to_timestamp(strip_tags(trim($b)));
      }
      $vars = json_decode($cache['vars'], TRUE);
      $cache_max = array_max($vars['matches']['date'], 'comp_date');
      $got_max = array_max($fetched_vars['matches']['date'], 'comp_date');
      if(comp_date($cache_max, $got_max))
        $ret = $vars;
      else
        $db->set_topic_cache($topic, $page, $topic_mode, $forum, json_encode($fetched_vars));
    } else
      $db->set_topic_cache($topic, $page, $topic_mode, $forum, json_encode($fetched_vars));
  }
  $ret['topic_mode'] = $topic_mode;
  return $ret;
}

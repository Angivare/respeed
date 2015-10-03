/*** Variables ***/

var form_data
  , edit_form_data
  , favoritesForums = []
  , favoritesTopics = []
  , isBigScreen = screen.width > 1024
  , liste_messages = liste_messages || []
  , googleAnalyticsID = $('meta[name="google-analytics-id"]').attr('content')
  , hasNiceTelInputType = navigator.userAgent.indexOf(' (iPhone; ') > -1 || navigator.userAgent.indexOf(' (iPod; ') > -1
  , ICStatsClicksMinusTouchstart = []
  , ICStatsLastFetch
  , hasTouch = 'createTouch' in document
  , isPageVisible = 'hidden' in document ? !document.hidden : true
  , originalPageTitle
  , sliderTopOffset = 999
  , isSliderSliding = false
  , refreshXhr
  , lastRefreshTimestamp = 0
  , handleRefreshInterval
  , handleRefreshTimeout
  , topicRefreshTimeout
  , draftWatcherInterval



/*** Helpers ***/

function ajax(action, data, success) {
  if (typeof $hash == 'undefined') { // Page d’erreur
    return
  }

  if (typeof success != 'undefined') {
    var dataType = 'json'
  }

  return $.ajax({
    method: 'POST',
    url: '/ajax/' + action + '.php?hash=' + $hash + '&ts=' + $ts + '&rand=' + $rand,
    data: data,
    success: success,
    dataType: dataType,
    timeout: 5000,
  })
  .fail(function(xhr) {
    if(xhr.status == 504) {
      success({
        rep: false,
        err: 'Timeout de JVC',
      })
    }
    else {
      success({
        rep: false,
        err: 'Erreur réseau',
      })
    }
  })
}

function tokenRefresh() {
  ajax('token_generate', {}, function(data) {
    if (!data.rep) {
      return
    }
    $hash = data.rep.hash
    $ts = data.rep.ts
    $rand = data.rep.rand
  })
}

function htmlentities(str) {
  return str.replace(/[\u00A0-\u9999<>\&]/g, function(i) {
    return '&#'+i.charCodeAt(0)+';'
  })
}

function updateFavorites() {
  if (!$is_connected) {
    return
  }
  if (localStorage.favoritesForums) {
    favoritesForums = JSON.parse(localStorage.favoritesForums)
  }
  if (localStorage.favoritesTopics) {
    favoritesTopics = JSON.parse(localStorage.favoritesTopics)
  }
  var favoritesLastUpdate = parseInt(localStorage.favoritesLastUpdate, 10) || 0
  var now = +new Date
  if (favoritesLastUpdate + (1000 * 60 * 10) > now) {
    return
  }
  ajax('favorites_get', {}, function(data) {
    if (!data.rep) {
      return
    }
    favoritesForums = []
    favoritesTopics = []
    $.each(data.rep.forums, function(index, value) {
      favoritesForums.push(value)
    })
    $.each(data.rep.topics, function(index, value) {
      favoritesTopics.push(value)
    })
    localStorage.favoritesForums = JSON.stringify(favoritesForums)
    localStorage.favoritesTopics = JSON.stringify(favoritesTopics)
    localStorage.favoritesLastUpdate = now

    displayFavorites()
    displayFavoritesOnIndex()
  })
}

function displayFavorites() {
  displayFavoritesForums()
  displayFavoritesTopics()
}

function displayFavoritesForums() {
  if (!$is_connected) {
    return
  }
  if (!isBigScreen) {
    return
  }
  if (!$('#forums_pref').length) {
    return
  }
  
  $('#forums_pref .menu-content').html('') // Suppression

  var hasThisForum = false
  $.each(favoritesForums, function (_, forum) {
    $('#forums_pref .menu-content').append('<li><a href="' + forum.lien + '">' + forum.titre + '</a></li>')
    if ($forum == forum.id) {
      hasThisForum = true
    }
  })
  if ($forum) {
    if (hasThisForum) {
      $('#forums_pref .menu-content').append('<li id="add_or_del_forum"><span><small>− Retirer ce forum</small></span></li>')
      $('#add_or_del_forum').click(delForum)
    }
    else {
      $('#forums_pref .menu-content').append('<li id="add_or_del_forum"><span><small>+ Ajouter ce forum</small></span></li>')
      $('#add_or_del_forum').click(addForum)
    }
  }
  $('#forums_pref').addClass('loaded')
}

function displayFavoritesTopics() {
  if (!$is_connected) {
    return
  }
  if (!isBigScreen) {
    return
  }
  if (!$('#topics_pref').length) {
    return
  }
  
  $('#topics_pref .menu-content').html('') // Suppression

  var hasThisTopic = false
  $.each(favoritesTopics, function (_, topic) {
    $('#topics_pref .menu-content').append('<li><a href="' + topic.lien + '">' + topic.titre + '</a></li>')
    if ($topic == topic.id) {
      hasThisTopic = true
    }
  })
  if ($topic) {
    if (hasThisTopic) {
      $('#topics_pref .menu-content').append('<li id="add_or_del_topic"><span><small>− Retirer ce topic</small></span></li>')
      $('#add_or_del_topic').click(delTopic)
    }
    else {
      $('#topics_pref .menu-content').append('<li id="add_or_del_topic"><span><small>+ Ajouter ce topic</small></span></li>')
      $('#add_or_del_topic').click(addTopic)
    }
  }
  $('#topics_pref').addClass('loaded')

  if ($('.js-slider').length) {
    sliderTopOffset = $('.js-slider').offset().top - 15
    makeFavoritesSlideable()
  }
}

function displayFavoritesOnIndex() {
  if (!$is_connected) {
    return
  }
  if (!$('.favorites-index').length) {
    return
  }

  $('.favorites-index .favorite').remove() // Suppression

  var odd = false
    , str = ''
  $.each(favoritesForums, function(_, forum) {
    str += '<a class="favorite ' + (odd ? 'odd' : '') + '" href="' + forum.lien + '">' + forum.titre + '</a>'
    odd = !odd
  })
  if (str) {
    $('.favorites-index .favorites-forums').append(str)
  }

  odd = false
  str = ''
  $.each(favoritesTopics, function(_, topic) {
    str += '<a class="favorite ' + (odd ? 'odd' : '') + '" href="' + topic.lien + '">' + topic.titre + '</a>'
    odd = !odd
  })
  if (str) {
    $('.favorites-index .favorites-topics').append(str)
  }
}

function request_form_data() {
  if (form_data) {
    return
  }
  var action = $('.form__topic').length ? 'topic_post' : 'message_post'
  ajax(action, {url: url}, function(data) {
    if (data.err == 'Forum fermé') {
      showErrors('Ce forum est fermé, vous ne pouvez pas y poster.', 'topic')
      $('.js-form-topic .form__topic').attr('disabled', '')
      $('.js-form-topic .form__textarea').attr('disabled', '')
      return
    }
    form_data = data.rep
    if (form_data.fs_signature) {
      showCaptcha(form_data.fs_signature)
    }
  })
}

function request_edit_form_data(e) {
  if (edit_form_data) {
    return
  }
  
  var id = $(this).closest('.message').attr('id')

  ajax('message_edit', {id_message: id}, function(data) {
    edit_form_data = data.rep
    if (edit_form_data.fs_signature) {
      $('#captcha-container-edit').html('<input class="input input-captcha" type="' + (hasNiceTelInputType ? 'tel' : 'number') + '" id="ccode_edit" name="ccode" placeholder="Code" autocomplete="off"> <img src="/ajax/captcha_get.php?'
        + 'signature=' + encodeURIComponent(edit_form_data.fs_signature)
        + '&hash=' + $hash + '&ts=' + $ts + '&rand=' + $rand
        + '" class="captcha">')
    }
  })
}

function startDraftWatcher() {
  draftWatcherInterval = setInterval(saveDraft, 500)
  $('.form__textarea').blur(stopDraftWatcher)
}

function stopDraftWatcher() {
  clearInterval(draftWatcherInterval)
}

function getDraftName() {
  if ($topic) {
    return 'draft_' + $topic
  }
  if ($forum) {
    return 'draft_f' + $forum
  }
}

function saveDraft() {
  localStorage.setItem(getDraftName(), $('.form__textarea').val())
}

function clearDraft() {
  localStorage.removeItem(getDraftName())
}

function insertDraft() {
  var draftName = getDraftName()
  if (!draftName) {
    return
  }
  var draft = localStorage.getItem(draftName)
  if (draft) {
    $('.form__textarea').val(draft)
  }
}

function addForum() {
  favoritesForums.push({
    lien: '/' + $forum + '-' + $slug,
    id: $forum,
    titre: $title,
  })
  localStorage.favoritesForums = JSON.stringify(favoritesForums)
  $('#forums_pref .menu-content').html('')
  displayFavoritesForums()

  ajax('favorites_update', {id: $forum, type: 'forum', action: 'add'})
}

function delForum() {
  var newFavoritesForums = []
  $.each(favoritesForums, function(_, forum) {
    if (forum.id != $forum) {
      newFavoritesForums.push(forum)
    }
  })
  favoritesForums = newFavoritesForums
  localStorage.favoritesForums = JSON.stringify(favoritesForums)
  $('#forums_pref .menu-content').html('')
  displayFavoritesForums()

  ajax('favorites_update', {id: $forum, type: 'forum', action: 'delete'})
}

function addTopic() {
  favoritesTopics.push({
    lien: '/' + $forum + '/' + $topic + '-' + $slug,
    id: $topic,
    titre: $title,
  })
  localStorage.favoritesTopics = JSON.stringify(favoritesTopics)
  $('#topics_pref .menu-content').html('')
  displayFavoritesTopics()

  ajax('favorites_update', {id: $topicNew, type: 'topic', action: 'add'})
}

function delTopic() {
  var newFavoritesTopics = []
  $.each(favoritesTopics, function(_, topic) {
    if (topic.id != $topic) {
      newFavoritesTopics.push(topic)
    }
  })
  favoritesTopics = newFavoritesTopics
  localStorage.favoritesTopics = JSON.stringify(favoritesTopics)
  $('#topics_pref .menu-content').html('')
  displayFavoritesTopics()

  ajax('favorites_update', {id: $topicNew, type:'topic', action: 'delete'})
}

/** Refresh **/

function handleRefreshOnPageChange(isInitialLoad) {
  clearTimeout(handleRefreshTimeout)
  clearInterval(handleRefreshInterval)
  clearTimeout(topicRefreshTimeout)
  if (refreshXhr) {
    refreshXhr.abort()
    refreshXhr = undefined // pour pas que cette condition soit true à chaque changement de page après la première
    lastRefreshTimestamp = 0
  }
  if ($topic) {
    handleRefreshInterval = setInterval(handleRefresh, 2000)
  }
}

function handleRefresh() {
  if (lastRefreshTimestamp < +new Date - 9050) {
    topicRefresh()
  }
}

function topicRefresh() {
  lastRefreshTimestamp = +new Date
  refreshXhr = ajax('topic_get', {
    forum: $forum,
    topic: $topic,
    slug: $slug,
    page: $page,
    last_page: lastPage,
    liste_messages: liste_messages,
  }, function(data) {
    if (!data.rep) {
      // Erreur
      return
    }

    data = data.rep

    if (data.topicNew != $topicNew || data.page != $page) {
      // On est plus sur le topic, ou alors plus sur la même page, quand la requête se termine
      return
    }

    // Titre du topic
    if (data.title != $title) {
      $title = data.title
      $('.js-topicTitle').html($title)
      $('title').html($title)
    }
    // Messages
    for (var i = 0; i < data.messages.length; i++) {
      var message = data.messages[i]
      if ($.inArray(message.id, liste_messages) > -1) {
        // Mise à jour

        // Date
        $('#' + message.id + ' .js-date').html(message.date)
        
        // Message
        if ($('#' + message.id).data('contentMd5') != message.contentMd5) {
          // Mise à jour
          $('#' + message.id).data('contentMd5', message.contentMd5)
          $('#' + message.id + ' .js-content').html(message.content)
          $('#' + message.id + ' .spoil').click(toggleSpoil)
          $('#' + message.id + ' .js-sticker').click(toggleStickerSize)

          triggerTabAlertForNewPosts()
        }
      }
      else {
        // Création
        $('.js-listeMessages').append(message.markup)
        liste_messages.push(message.id)

        if (message.pseudo.toLowerCase() != myPseudo.toLowerCase()) {
          triggerTabAlertForNewPosts()
        }

        $('#' + message.id + ' .js-quote').click(quote)
        $('#' + message.id + ' .js-edit').click(edit)
        $('#' + message.id + ' .js-delete').click(deleteMessage)
        $('#' + message.id + ' .js-profile').click(openProfile)
        $('#' + message.id + ' .js-menu').click(toggleMenu)
        $('#' + message.id).click(closeMenu)
        $('#' + message.id + ' .spoil').click(toggleSpoil)
        $('#' + message.id + ' .js-sticker').click(toggleStickerSize)
      }
    }

    // Pagination
    if (data.last_page != lastPage) {
      lastPage = data.last_page
      $('.pagination-topic__pages').html(data.paginationMarkup)
      triggerTabAlertForNewPosts()
    }

    topicRefreshTimeout = setTimeout(topicRefresh, 2000)
  })
}

/** /Refresh **/

function cancelEdit() {
  if ($('.js-isEditing').length) {
    $('.js-isEditing').html($('.js-isEditing').data('html')).removeClass('js-isEditing')
    return true
  }
  return false
}

function processICStats() {
  localStorage.ICStatsClicksMinusTouchstart2 = ICStatsClicksMinusTouchstart.join(' ')
  
  if (ICStatsClicksMinusTouchstart.length >= 15) {
    $.post('/collect_icstats.php', {clicks_minus_touchstart: localStorage.ICStatsClicksMinusTouchstart2})
    ICStatsClicksMinusTouchstart = []
    localStorage.removeItem('ICStatsClicksMinusTouchstart2')
  }
}

function getLinkTarget(target) {
  while (target && target.nodeName != 'A') {
    target = target.parentNode
  }
  return target
}

function triggerTabAlertForNewPosts() {
  if (isPageVisible || originalPageTitle) {
    return
  }
  $('.js-favicon').prop('href', '/images/favicon-newposts.png')
  originalPageTitle = document.title
  document.title = '♥ ' + originalPageTitle
}

function removeTabAlertForNewPosts() {
  if (!originalPageTitle) {
    return
  }
  $('.js-favicon').prop('href', '/images/favicon.png')
  document.title = originalPageTitle
  originalPageTitle = false
}

function makeFavoritesSlideable() {
  if (!isBigScreen) {
    return
  }

  adjustSliderWidth()
  $(window).resize(adjustSliderWidth)

  makeFavoritesSlide()
  $(window).scroll(makeFavoritesSlide)
  $(window).resize(makeFavoritesSlide)
}

function makeFavoritesSlide() {
  if (scrollY > sliderTopOffset) {
    if (!isSliderSliding) {
      $('.js-slider').addClass('sliding')
      isSliderSliding = true
    }
  }
  else {
    if (isSliderSliding) {
      $('.js-slider').removeClass('sliding')
      isSliderSliding = false
    }
  }
}

function adjustSliderWidth() {
  // Parce que la taille ne dépend plus du parent en position fixed
  $('#topics_pref').css('width', $('#forums_pref').width())
}

function showCaptcha(signature) {
  $('.form__captcha-container')
  .html('<input class="js-captcha input input-captcha" type="' + (hasNiceTelInputType ? 'tel' : 'number') + '" id="ccode" name="ccode" placeholder="Code" autocomplete="off" tabindex="3"> '
    + '<img src="/ajax/captcha_get.php?'
    + 'signature=' + encodeURIComponent(signature)
    + '&hash=' + $hash + '&ts=' + $ts + '&rand=' + $rand
    + '" class="captcha">')
  .addClass('shown')
}

function showErrors(errors) {
  $('.form .form__errors p').html(errors)
  $('.form .form__errors').show()
  $('.form .form__textarea').focus()
}



/*** Fonctions pour events ***/

function post(e) {
  e.preventDefault()
  if (!form_data) {
    $('.form .form__textarea').focus()
    return
  }
  var params = {
    url: url,
    msg: $('.form .form__textarea').val(),
    form: form_data,
  }
  if ($('.js-captcha').val()) {
    params.ccode = $('.js-captcha').val()
  }
  if ($('.form__topic').length) {
    params.title = $('.form__topic').val()
  }
  var action = $('.form__topic').length ? 'topic_post' : 'message_post'
  ajax(action, params, function(data) {
    $('.form .form__captcha-container')
    .html('')
    .removeClass('shown')

    form_data = null

    if (data.rep) {
      $('.form .form__errors').hide()
      $('.form .form__textarea').val('')

      clearDraft()

      if (data.rep !== true)
        window.location.href = data.rep

      return
    }

    showErrors(data.err, 'post')
  })
}

function postEdit(e) {
  if (!edit_form_data) {
    $('#editmessage').focus()
    return
  }

  var id = $(this).closest('.message').attr('id')

  var params = {
    id_message: id,
    msg: $('#editmessage').val(),
    form: edit_form_data,
  }
  if ($('#ccode_edit').val()) {
    params.ccode = $('#ccode_edit').val()
  }
  ajax('message_edit', params, function(data) {
    $('#captcha-container-edit').html('')
    edit_form_data = null

    if (data.rep) {
      $('.form-error-edit').hide()
      cancelEdit()
      return
    }

    $('.form-error-edit p').html(data.err)
    $('.form-error-edit').show()
    $('#editmessage').focus()
  })
}

function quote() {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')
  
  var html = $('#' + id + ' .message__content-text').html().trim()

  var text = JVCode.toJVCode(html)

  var citation = ""
  if ($('#newmessage').val() && !/\n\n$/.test($('#newmessage').val())) {
    if (!/\n$/.test($('#newmessage').val())) {
      citation = "\n"
    }
    else {
      citation = "\n\n"
    }
  }
  citation += "> '''" + pseudo + "''' :\n"
  citation += "> \n"
  citation += "> " + text.split("\n").join("\n> ")
  citation += "\n\n"
  
  $('.js-form-post .form__textarea').focus() // Doit être avant .val pour avoir le curseur placé en bas
  $('.js-form-post .form__textarea').val($('.js-form-post .form__textarea').val() + citation)
}

function edit() {
  if (cancelEdit()) {
    return
  }

  var id = $(this).closest('.message').attr('id')
  
  var html = $('#' + id + ' .message__content-text').html().trim()

  var text = htmlentities(JVCode.toJVCode(html))

  var htmlTextarea = '<div class="form-error form-error-edit"><p></p></div>\
  <p>\
    <textarea class="input textarea" id="editmessage">' + text + '</textarea>\
    <span id="captcha-container-edit"></span>\
    <br><input class="submit submit-main submit-big" id="post_edit" type="submit" value="Poster">\
  </p>'
  $('#' + id + ' .js-content').html(htmlTextarea).addClass('js-isEditing').data('html', html)
  $('#' + id + ' .js-content textarea').focus(request_edit_form_data).focus()
  $('#post_edit').click(postEdit)
}

function deleteMessage() {
  var id = $(this).closest('.message').attr('id')

  if (!confirm('Effacer définitivement ce message ?')) {
    return
  }
  $('#' + id).remove()
  ajax('message_delete', {id_message: id})
}

function openProfile() {
  window.open(this.href, "_blank", "toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no,width=520,height=570,left=" + (screen.width / 2 - 520 / 2) + ",top=" + (screen.height / 2 - 570 / 2 - 20))
  return false
}

function toggleMenu() {
  var id = $(this).closest('.message').attr('id')
  $('#' + id).toggleClass('message--menu-opened')
}

function closeMenu(e) {
  if ($(e.target).hasClass('js-menu')) {
    return
  }
  var id = this.id
  $('#' + id).removeClass('message--menu-opened')
}

function toggleSpoil(event) {
  event.stopPropagation()
  var target = $(event.target)
  if (target.is('.sticker') || target.is('a')) {
    return
  }
  $(this).toggleClass('spoil--revealed')
}

function toggleStickerSize() {
  var isAlreadyEnlarged = $(this).hasClass('sticker--enlarged')
    , isBig = $(this).hasClass('sticker--big')
    , code = $(this).data('sticker-id')

  if (!isAlreadyEnlarged) {
    if (devicePixelRatio > 1.5) {
      $(this).prop('src', 'http://jv.stkr.fr/p7s/' + code)
    }
    $(this).addClass('sticker--enlarged')
  }
  else {
    $(this).prop('src', 'http://jv.stkr.fr/p3w/' + code)
    $(this).removeClass('sticker--enlarged')
  }
}

function handleVisibilityChange() {
  isPageVisible = !document.hidden
  if (isPageVisible) {
    removeTabAlertForNewPosts()
  }
}

function goToForm() {
  if (!$('#newmessage')) {
    return
  }
  $('.js-form-post .form__textarea').focus()
  scrollTo(0, $('.js-form-post').offset().top + 1)
}



/*** App ***/

if (!$is_connected) {
  localStorage.clear()
}

setInterval(tokenRefresh, (30-2)*60*1000)

if (googleAnalyticsID) {
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', googleAnalyticsID, 'auto');
  
  instantClick.on('change', function() {
    ga('set', 'dimension1', $is_connected ? 'Member' : 'Guest')
    ga('send', 'pageview', location.pathname + location.search)
  })
}

instantClick.on('change', function(isInitialLoad) {
  FastClick.attach(document.body)
  updateFavorites()
  setTimeout(displayFavorites, 0) // Marche pas sans timer (mettre un timer pour IC ?)
  displayFavoritesOnIndex()
  handleRefreshOnPageChange(isInitialLoad)
  stopDraftWatcher()
  insertDraft()
})

instantClick.on('restore', function() {
  handleRefreshOnPageChange()
  $('.form').submit(post)
})

instantClick.on('change', function() {
  $('.form').submit(post)
  $('.js-form-topic .form__topic').focus(request_form_data)
  $('.js-form-post .form__textarea').focus(request_form_data)
  $('.form__textarea').focus(startDraftWatcher)

  // Messages
  $('.js-quote').click(quote)
  $('.js-edit').click(edit)
  $('.js-delete').click(deleteMessage)
  $('.js-profile').click(openProfile)
  $('.js-menu').click(toggleMenu)
  $('.message').click(closeMenu)
  $('.spoil').click(toggleSpoil)
  $('.js-sticker').click(toggleStickerSize)
  $('.js-button-go-to-form').click(goToForm)
})

instantClick.init()

if (hasTouch) {
  document.addEventListener('touchstart', function(e) {
    if (!getLinkTarget(e.target)) {
      return
    }
    ICStatsLastFetch = +new Date
  }, true)

  document.addEventListener('click', function(e) {
    if (!ICStatsLastFetch || !getLinkTarget(e.target)) {
      return
    }
    if ('ICStatsClicksMinusTouchstart' in localStorage && localStorage.ICStatsClicksMinusTouchstart2 != ICStatsClicksMinusTouchstart.join(' ')) {
      ICStatsClicksMinusTouchstart = localStorage.ICStatsClicksMinusTouchstart2.split(' ')
    }
    ICStatsClicksMinusTouchstart.push(+new Date - ICStatsLastFetch)
    ICStatsLastFetch = false
    processICStats()
  }, true)

  localStorage.removeItem('ICStatsClicksMinusTouchstart')
}

document.addEventListener('visibilitychange', handleVisibilityChange)

localStorage.removeItem('blacklist')
localStorage.removeItem('remoteBlacklistLastUpdate')

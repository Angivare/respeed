/*** Variables ***/

var form_data
  , edit_form_data
  , favoritesForums = []
  , favoritesTopics = []
  , isBigScreen = screen.width > 1024
  , liste_messages = liste_messages || []
  , googleAnalyticsID = $('meta[name="google-analytics-id"]').attr('content')
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
  , toastTimer
  , topicPositionLastMessageId



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
    timeout: 9000,
  })
  .fail(function(xhr) {
    if (xhr.status == 504) {
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

/** Favorites **/

function updateFavorites() {
  var page = 'random'
    , forumSum = $('.js-favorites-forums').data('sum')
    , topicSum = $('.js-favorites-topics').data('sum')

  if ($('.js-favorites-index').length) {
    page = 'index'
  }
  else if ($('.js-favorites').length) {
    page = 'forum_or_topic'
  }
  ajax('favorites_update', {
    page: page,
    forum_sum: forumSum,
    topic_sum: topicSum,
  }, function(data) {
    if (data.html.forums) {
      $('.js-favorites-forums').html(data.html.forums)
      $('.js-favorites-forums').data('sum', data.html.forumSum)
    }
    if (data.html.topics) {
      $('.js-favorites-topics').html(data.html.topics)
      $('.js-favorites-topics').data('sum', data.html.topicSum)
    }
  })
}

function toggleFavorite() {
  var action = $('.js-favorite-toggle').data('action')
    , type = $topicIdNew ? 'topic': 'forum'
    , id = $topicIdNew ? $topicIdNew : $forum
    , forumSum = $('.js-favorites-forums').data('sum')
    , topicSum = $('.js-favorites-topics').data('sum')

  ajax('favorites_toggle', {
    id: id,
    type: type,
    action: action,
    forum_sum: forumSum,
    topic_sum: topicSum,
  }, function(data) {
    if (!data.html) {
      return false
    }
    if (action == 'add') {
      $('.aside .js-favorite-toggle').removeClass('aside__top-button--favorite').addClass('aside__top-button--unfavorite')
      $('.js-favorite-toggle-label').html('Retirer des favoris')
      $('.js-favorite-toggle').data('action', 'delete')
      $('.js-add-to-favorite-mobile-shortcut').remove()
      showToast('Mis en favoris')
    }
    else {
      $('.aside .js-favorite-toggle').removeClass('aside__top-button--unfavorite').addClass('aside__top-button--favorite')
      $('.js-favorite-toggle-label').html('Mettre en favoris')
      $('.js-favorite-toggle').data('action', 'add')
      showToast('Retiré des favoris')
    }
    if (data.html.forums) {
      $('.js-favorites-forums').html(data.html.forums)
      $('.js-favorites-forums').data('sum', data.html.forumSum)
    }
    if (data.html.topics) {
      $('.js-favorites-topics').html(data.html.topics)
      $('.js-favorites-topics').data('sum', data.html.topicSum)
    }
  })
}

/** /Favorites **/


/** Toast **/

function showToast(message, duration_in_seconds) {
  if (!duration_in_seconds) {
    duration_in_seconds = 1.5
  }
  clearTimeout(toastTimer)
  $('.toast').addClass('toast--shown')
  $('.toast__label').text(message)
  toastTimer = instantClick.timer(hideToast, duration_in_seconds * 1000)
}

function hideToast() {
  $('.toast').removeClass('toast--shown')
  toastTimer = instantClick.timer(function() {
    $('.toast__label').text(' ')
  }, 150)
}

/** /Toast **/


/** Request form data **/

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

function request_edit_form_data() {
  if (edit_form_data) {
    return
  }

  var id = $(this).closest('.message').attr('id')

  ajax('message_edit', {id_message: id}, function(data) {
    edit_form_data = data.rep
    if (edit_form_data.fs_signature) {
      $('#captcha-container-edit').html('<small>Un captcha est présent.</small>')
    }
  })
}

/** /Request form data **/


/** Refresh **/

function handleRefreshOnPageChange() {
  clearTimeout(handleRefreshTimeout)
  clearInterval(handleRefreshInterval)
  clearTimeout(topicRefreshTimeout)
  if (refreshXhr) {
    refreshXhr.abort()
    refreshXhr = undefined // pour pas que cette condition soit true à chaque changement de page après la première
    lastRefreshTimestamp = 0
  }
  if ($topicIdNew) {
    topicRefresh()
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
  refreshXhr = ajax('topic_refresh', {
    forum: $forum,
    topic_mode: $topicMode,
    topic_id_old_or_new: $topicIdOldOrNew,
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
        if ($('#' + message.id).data('contentChecksum') != message.contentChecksum) {
          // Mise à jour
          $('#' + message.id).data('contentChecksum', message.contentChecksum)
          $('#' + message.id + ' .js-content').html(message.content)
          $('#' + message.id + ' .spoil').click(toggleSpoil)
          $('#' + message.id + ' .sticker').click(toggleStickerSize)
        }
      }
      else {
        // Création
        $('.js-listeMessages').append(message.markup)

        if (message.pseudo.toLowerCase() != myPseudo.toLowerCase() && !isInBlacklist(message.pseudo)) {
          triggerTabAlertForNewPosts()
        }

        $('#' + message.id + ' .js-quote').click(quote)
        $('#' + message.id + ' .js-edit').click(edit)
        $('#' + message.id + ' .js-delete').click(deleteMessage)
        $('#' + message.id + ' .js-menu').click(toggleMenu)
        $('#' + message.id + ' .message__ignored-notice_show-message-button').click(showBlacklistedMessage)
        $('#' + message.id).click(closeMenu)
        $('#' + message.id + ' .spoil').click(toggleSpoil)
        $('#' + message.id + ' .sticker').click(toggleStickerSize)
      }
    }

    // Pagination
    if (data.last_page != lastPage) {
      lastPage = data.last_page
      $('.pagination-topic__pages').html(data.paginationMarkup)
      triggerTabAlertForNewPosts()
    }

    topicRefreshTimeout = setTimeout(topicRefresh, 2000)

    updateTopicPosition()
  })
  instantClick.trackXhr(refreshXhr)
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
  if (!$('.js-slider').length) {
    return
  }

  sliderTopOffset = $('.js-slider').offset().top - 15

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
  $('.js-slider').css('width', $('.menu.js-favorites-forums').width())
}

function showCaptcha() {
  $('.form__captcha-container')
  .html('<small>Un captcha est présent.</small>')
  .addClass('shown')
}

function showErrors(errors) {
  $('.form .form__errors p').html(errors)
  $('.form .form__errors').show()
  $('.form .form__textarea').focus()
}

function showLoadedToast() {
  // http://stackoverflow.com/questions/10730362/get-cookie-by-name
  var parts = ("; " + document.cookie).split("; toast=")
  if (parts.length != 2) {
    return false
  }
  var message = atob(decodeURIComponent(parts.pop().split(";").shift()))

  showToast(message)

  document.cookie = 'toast=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;'
}

function handleBlacklist() {
  if ($blacklistNeedsUpdate) {
    updateBlacklist()
  }
  setInterval(updateBlacklist, 1000 * 60 * 5)
}

function updateBlacklist() {
  $blacklistNeedsUpdate = false
  ajax('blacklist_update', {}, function(data) {
    $blacklist = data['rep']['array']
    $('#blacklist-style').html(data['rep']['style'])
  })
}

function isInBlacklist(pseudo) {
  return $.inArray(pseudo.toLowerCase(), $blacklist) > -1
}

function updateTopicPosition() {
  if (!$topicIdNew) {
    return
  }
  if (topicPositionLastMessageId >= liste_messages[liste_messages.length - 1]) {
    return
  }
  topicPositionLastMessageId = liste_messages[liste_messages.length - 1]
  var nbAnswers = ($page - 1) * 20 + liste_messages.length - 1
  ajax('topic_update_position', {
    topic_id_new: $topicIdNew,
    message_id: topicPositionLastMessageId,
    nb_answers: nbAnswers,
    last_page: lastPage,
  })
}

function addTopicVisitedPage() {
  ajax('topic_add_visited_page', {
    topic_id_new: $topicIdNew,
    page: $page,
  })
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

function postEdit() {
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
  updateTopicPosition()
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
  if (target.is('a, .sticker, .noelshack-link__thumb')) {
    return
  }
  $(this).toggleClass('spoil--revealed')
}

function toggleStickerSize() {
  var isAlreadyEnlarged = $(this).hasClass('sticker--enlarged')
    , urlCode = $(this).prop('src').split('/').pop()
    , id = $(this).data('sticker-id')
    , isUnknown = $(this).hasClass('sticker--unknown')

  if (!isAlreadyEnlarged) {
    $(this).prop('src', isUnknown ? ('http://jv.stkr.fr/p7s/' + id) : ('/images/stickers/big/' + urlCode))
    $(this).addClass('sticker--enlarged')
  }
  else {
    $(this).prop('src', isUnknown ? ('http://jv.stkr.fr/p3w/' + id) : ('/images/stickers/small/' + urlCode))
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

function showBlacklistedMessage() {
  var message = $(this).closest('.message')
    , pseudo = message.attr('data-pseudo')

  var message = $(this).closest('.message')
  message.removeClass('message-by--' + pseudo.toLowerCase())
}

function toggleBlacklist() {
  var pseudo = $(this).attr('data-pseudo')
    , action = isInBlacklist(pseudo) ? 'remove' : 'add'

  ajax('blacklist_toggle', {pseudo: pseudo, action: action}, function() {
    location.href = location.href
  })
}

function toggleMobileMenu() {
  $('.mobile-menu').toggleClass('mobile-menu--opened')
}



/*** App ***/

if (googleAnalyticsID) {
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', googleAnalyticsID, 'auto');

  instantClick.on('change', function() {
    ga('set', 'dimension1', 'Member')
    ga('send', 'pageview', location.pathname + location.search)
  })
}

instantClick.on('change', function(isInitialLoad) {
  topicPositionLastMessageId = 0

  FastClick.attach(document.body)
  handleRefreshOnPageChange(isInitialLoad)
  handleBlacklist()
  makeFavoritesSlideable()
  showLoadedToast()
  updateTopicPosition()
  instantClick.timer(updateFavorites, (60 * 10 - $freshness) * 1000)
  instantClick.interval(tokenRefresh, 29 * 60 * 1000)

  $('.js-form-topic, .js-form-post').submit(post)
  $('.js-form-topic .form__topic').focus(request_form_data)
  $('.js-form-post .form__textarea').focus(request_form_data)
  $('.mobile-menu__opener').click(toggleMobileMenu)
  $('.mobile-menu__item').click(toggleMobileMenu)
  $('.js-favorite-toggle').click(toggleFavorite)

  // Messages
  $('.js-quote').click(quote)
  $('.js-edit').click(edit)
  $('.js-delete').click(deleteMessage)
  $('.js-menu').click(toggleMenu)
  $('.message').click(closeMenu)
  $('.spoil').click(toggleSpoil)
  $('.sticker').click(toggleStickerSize)
  $('.js-button-go-to-form').click(goToForm)
  $('.message__ignored-notice_show-message-button').click(showBlacklistedMessage)
  $('.blacklist-toggle').click(toggleBlacklist)
})

instantClick.on('restore', function() {
  handleRefreshOnPageChange()
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

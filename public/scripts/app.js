/*** Variables ***/

var form_data
  , edit_form_data
  , blacklist
  , favoritesForums = []
  , favoritesTopics = []
  , isBigScreen = screen.width > 1024
  , topicRefreshes = []
  , liste_messages = liste_messages || []



/*** Helpers ***/

function ajax(action, data, success) {
  if(typeof(success) !== 'undefined')
    var dataType = 'json'
  $.post('/ajax/' + action + '.php?hash=' + $hash + '&ts=' + $ts + '&rand=' + $rand, data, success, dataType)
}

function updateLocalBlacklist() {
  blacklist = (localStorage.blacklist || '').split(' ')
  if (!blacklist[blacklist.length - 1]) {
    blacklist.pop()
  }
}

function addToBlacklist(pseudo, id_message) {
  pseudo = pseudo.toLowerCase()
  if ($.inArray(pseudo, blacklist) >= 0) {
    return
  }
  localStorage.blacklist = pseudo + ' ' + (localStorage.blacklist || '')
  updateLocalBlacklist()
  if (id_message) {
    ajax('blacklist_add', {id_message: id_message})
  }
}

function removeFromBlacklist(pseudo) {
  updateLocalBlacklist()
  pseudo = pseudo.toLowerCase()
  var index = $.inArray(pseudo, blacklist)
  if (index == -1) {
    return
  }
  var blacklistString = ''
  blacklist.splice(index, 1)
  localStorage.blacklist = blacklist.join(' ')
  ajax('blacklist_remove', {nick: pseudo})
}

function applyBlacklist() {
  $('[data-pseudo]').each(function(i, element) {
    var pseudo = ($(element).data('pseudo') + '').toLowerCase()

    if ($(element).hasClass('ignored')) {
      if ($.inArray(pseudo, blacklist) == -1) {
        $(element).removeClass('ignored')
      }
    }

    if ($.inArray(pseudo, blacklist) >= 0) {
      $(element).addClass('ignored')
    }
  })
}

function updateRemoteBlacklist() {
  if (!$is_connected) {
    return
  }
  var remoteBlacklistLastUpdate = localStorage.remoteBlacklistLastUpdate || 0
  var now = +new Date
  if (remoteBlacklistLastUpdate + (1000 * 60 * 60) < now) {
    ajax('blacklist_get', {}, function(data) {
      var remoteBlacklist = data.rep
      for (var i = 0; i < remoteBlacklist.length; i++) {
        addToBlacklist(remoteBlacklist[i].human)
      }
      localStorage.remoteBlacklistLastUpdate = now
    })
  }
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
  var favoritesLastUpdate = localStorage.favoritesLastUpdate || 0
  var now = +new Date
  if (favoritesLastUpdate + (1000 * 60 * 60) > now) {
    return
  }
  ajax('favorites_get', {}, function(data) {
    if (!data.rep) {
      alert('Erreur lors de la synchronisation des favoris : ' + data.err)
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
  $('#forums_pref').show()
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
  $('#topics_pref').show()
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
  var action = $('#newsujet').length ? 'topic_post' : 'message_post'
  ajax(action, {url: url}, function(data) {
    if (data.err == 'Forum fermé') {
      $('.form-error p').html('Ce forum est fermé, vous ne pouvez pas y poster.')
      $('#newsujet').attr('disabled', '')
      $('#newmessage').attr('disabled', '')
      $('.form-error').show()
      return
    }
    form_data = data.rep
    if (form_data.fs_signature) {
      $('#captcha-container').html('<br><input class="input input-captcha" id="ccode" name="ccode" placeholder="Code" autocomplete="off"> <img src="/ajax/captcha_get.php?'
        + 'signature=' + encodeURIComponent(form_data.fs_signature)
        + '&hash=' + $hash + '&ts=' + $ts + '&rand=' + $rand
        + '" class="captcha">')
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
      $('#captcha-container').html('<br><input class="input input-captcha" id="ccode_edit" name="ccode" placeholder="Code" autocomplete="off"> <img src="/ajax/captcha_get.php?'
        + 'signature=' + encodeURIComponent(edit_form_data.fs_signature)
        + '&hash=' + $hash + '&ts=' + $ts + '&rand=' + $rand
        + '" class="captcha">')
    }
  })
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

function topicRefresh() {
  if (!$topic) {
    // On est pas sur un topic
    return
  }

  ajax('topic_get', {forum: $forum, topic: $topic, slug: $slug, page: $page, liste_messages: liste_messages}, function(data) {
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
        }
      }
      else {
        // Création
        $('.js-listeMessages').append(message.markup)
        liste_messages.push(message.id)

        $('#' + message.id + ' .meta-quote').click(quote)
        $('#' + message.id + ' .meta-ignore').click(ignore)
        $('#' + message.id + ' .meta-unignore').click(unignore)
        $('#' + message.id + ' .meta-edit').click(edit)
        $('#' + message.id + ' .meta-delete').click(deleteMessage)
        $('#' + message.id + ' .m-profil').click(openProfile)
        $('#' + message.id + ' .meta-menu').click(toggleMenu)
        $('#' + message.id + ' .message').click(closeMenu)
        $('#' + message.id + ' .js-avatarImg').error(remove404Avatar)
      }
    }
    
    // Pagination
    if (data.last_page > last_page) {
      last_page = data.last_page
      $('.pages-container').html(data.paginationMarkup)
    }
  })
}

function cancelEdit() {
  if ($('.js-isEditing').length) {
    $('.js-isEditing').html($('.js-isEditing').data('html')).removeClass('js-isEditing')
    return true
  }
  return false
}



/*** Fonctions pour events ***/

function post(e) {
  e.preventDefault() // Pas sûr que ce soit nécessaire, cliquer le bouton ne fait rien au moins sur Chrome
  if (!form_data) {
    $('#newmessage').focus()
    return
  }
  var params = {
    url: url,
    msg: $('#newmessage').val(),
    form: form_data,
  }
  if ($('#ccode').val()) {
    params.ccode = $('#ccode').val()
  }
  if ($('#newsujet')) {
    params.title = $('#newsujet').val()
  }
  var action = $('#newsujet').length ? 'topic_post' : 'message_post'
  ajax(action, params, function(data) {
    $('#captcha-container').html('')
    form_data = null

    if (data.rep) {
      $('.form-error').hide()
      $('#newmessage').val('')
      return
    }

    $('.form-error p').html(data.err)
    $('.form-error').show()
    $('#newmessage').focus()
  })
}

function postEdit(e) {
  e.preventDefault() // Pas sûr que ce soit nécessaire, cliquer le bouton ne fait rien au moins sur Chrome
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
    params.ccode = $('#ccode').val()
  }
  ajax('message_edit', params, function(data) {
    $('#captcha-container').html('')
    edit_form_data = null

    if (data.rep) {
      $('.form-error').hide()
      cancelEdit()
      return
    }

    $('.form-error p').html(data.err)
    $('.form-error').show()
    $('#editmessage').focus()
  })
}

function ignore() {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')

  if (!$is_connected) {
    location.href = '/se_connecter?pour=ignorer&qui=' + pseudo
    return
  }

  addToBlacklist(pseudo, id)
  applyBlacklist()
}

function unignore() {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')

  removeFromBlacklist(pseudo)
  applyBlacklist()
}

function quote() {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')
  
  var html = $('#' + id + ' .content').html()
    , pos = html.indexOf('<p class="edit-mention">')
  if (pos > -1) {
    html = html.substr(0, pos)
  }

  var text = toJVCode(html)

  if (!$is_connected) {
    location.href = '/se_connecter?pour=citer&qui=' + pseudo
    return
  }

  var citation = ""
  if ($('#newmessage').val() && !/\n\n$/.test($('#newmessage').val())) {
    citation += "\n\n"
  }
  citation += "> '''" + pseudo + "''', http://jvforum.fr" + location.pathname + "#" + id + "\n"
  citation += "> \n"
  citation += "> " + text.split("\n").join("\n> ")
  citation += "\n\n"
  
  $('#newmessage').focus() // Doit être avant .val pour avoir le curseur placé en bas
  $('#newmessage').val($('#newmessage').val() + citation)
}

function edit() {
  if (cancelEdit()) {
    return
  }

  var id = $(this).closest('.message').attr('id')
  
  var html = $('#' + id + ' .content').html()
    , pos = html.indexOf('<p class="edit-mention">')
  if (pos > -1) {
    html = html.substr(0, pos)
  }

  var text = JVCode.he(toJVCode(html))

  var htmlTextarea = '<p>\
    <textarea class="input textarea" id="editmessage">' + text + '</textarea>\
    <span id="captcha-container"></span>\
    <br><input class="submit submit-main submit-big" id="post_edit" type="submit" value="Poster">\
  </p>'
  $('#' + id + ' .js-content').html(htmlTextarea).addClass('js-isEditing').data('html', html)
  $('#' + id + ' .js-content textarea').focus(request_edit_form_data).focus()
  $('#post_edit').click(postEdit)
}

function deleteMessage() {
  var id = $(this).closest('.message').attr('id')

  if (!confirm('Êtes-vous sûr de vouloir effacer ce message ? Vous ne pourrez pas le restaurer depuis JVForum.')) {
    return
  }
  $('#' + id).remove()
  ajax('message_delete', {id_message: id})
}

function openProfile() {
  window.open(this.href, "_blank", "toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no,width=520,height=570,left=" + (screen.width / 2 - 520 / 2) + ",top=" + (screen.height / 2 - 570 / 2 - 20))
  return false
}

function floatingNewmessageTap() {
  if (!$is_connected) {
    location.href = '/se_connecter?pour=poster&forum=' + $forum + '&topic=' + $topic + '&slug=' + $slug
    return
  }
}

function toggleMenu(e) {
  var id = e.target.parentNode.parentNode.parentNode.parentNode.id
  $('#' + id).toggleClass('show-menu')
}

function closeMenu(e) {
  if (e.target.className == 'meta-menu') {
    return
  }
  var id = this.id
  $('#' + id).removeClass('show-menu')
}

function remove404Avatar(e) {
  $(e.target).remove()
}



/*** App ***/

if (!$is_connected) {
  localStorage.clear()
}

updateRemoteBlacklist()
setInterval(topicRefresh, 2500)

InstantClick.on('change', function(isInitialLoad) {
  FastClick.attach(document.body)
  updateFavorites()
  setTimeout(displayFavorites, 0) // Marche pas sans timer (mettre un timer pour IC ?)
  updateLocalBlacklist()
  applyBlacklist()
  displayFavoritesOnIndex()
})

InstantClick.on('change', function() {
  $('#post').click(post)
  $('#newsujet').focus(request_form_data)
  $('#newmessage').focus(request_form_data)
  $('#floating_newmessage').click(floatingNewmessageTap)

  // Messages
  $('.meta-quote').click(quote)
  $('.meta-ignore').click(ignore)
  $('.meta-unignore').click(unignore)
  $('.meta-edit').click(edit)
  $('.meta-delete').click(deleteMessage)
  $('.m-profil').click(openProfile)
  $('.meta-menu').click(toggleMenu)
  $('.message').click(closeMenu)
  $('.js-avatarImg').error(remove404Avatar)
})

InstantClick.init()

/*** Variables ***/

var form_data
  , blacklist
  , favoritesForums = []
  , favoritesTopics = []
  , isBigScreen = screen.width > 1024

/*** Helpers ***/

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
    $.get('/ajax/blacklist_add.php', {id_message: id_message})
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
  $.get('/ajax/blacklist_remove.php', {nick: pseudo})
}

function applyBlacklist() {
  $('[data-pseudo]').each(function(i, element) {
    var pseudo = $(element).data('pseudo').toLowerCase()

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
    $.getJSON('/ajax/blacklist_get.php', function(data) {
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
  $.getJSON('/ajax/favorite_get.php', function(data) {
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
  if (!$('#forums_pref')) {
    return
  }

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
  if (!$('#topics_pref')) {
    return
  }

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

function request_form_data() {
  if (!form_data) {
    $.post($('#newsujet') ? '/ajax/post_topic.php' : '/ajax/post_msg.php', {url: url}, function(data, status, xhr) {
      data = JSON.parse(data)
      if (data.err == 'Forum fermé') {
        $('.form-error p').html('Ce forum est fermé, vous ne pouvez pas y poster.')
        $('#newsujet').attr('disabled', '')
        $('#newmessage').attr('disabled', '')
        $('.form-error').show()
        return
      }
      form_data = data.rep
      if (form_data.fs_signature) {
        $('#captcha-container').html('<br><input class="input input-captcha" id="ccode" name="ccode" placeholder="Code"> <img src="/ajax/captcha.php?signature=' + form_data.fs_signature + '" class="captcha">')
      }
    })
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

  $.get('/ajax/favorites_update.php', {
    id: $forum,
    type: 'forum',
    action: 'add',
  })
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

  $.get('/ajax/favorites_update.php', {
    id: $forum,
    type: 'forum',
    action: 'delete',
  })
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

  $.get('/ajax/favorites_update.php', {
    id: $topicNew,
    type: 'topic',
    action: 'add',
  })
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

  $.get('/ajax/favorites_update.php', {
    id: $topicNew,
    type: 'topic',
    action: 'delete',
  })
}

/*** App ***/

$(function() {
  updateLocalBlacklist()
  updateRemoteBlacklist()
  applyBlacklist()
  updateFavorites()
  displayFavorites()
})

$('#post').click(function(e) {
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
  $.post($('#newsujet') ? '/ajax/post_topic.php' : '/ajax/post_msg.php', params, function(data, status, xhr) {
    data = JSON.parse(data)

    $('#captcha-container').html('')
    form_data = null

    if (!data.err) {
      $('.form-error').hide()
      $('#newmessage').val('')
      return
    }

    $('.form-error p').html(data.err)
    $('.form-error').show()
    $('#newmessage').focus()
  })
})

$('#newsujet').focus(function(e) {
  request_form_data()
})

$('#newmessage').focus(function() {
  request_form_data()
})

$('.meta-ignore').click(function(e) {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')

  if (!$is_connected) {
    location.href = '/se_connecter?pour=ignorer&qui=' + pseudo
    return
  }

  addToBlacklist(pseudo, id)
  applyBlacklist()
})

$('.meta-unignore').click(function(e) {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')

  removeFromBlacklist(pseudo)
  applyBlacklist()
})

$('.meta-quote').click(function(e) {
  var id = $(this).closest('.message').attr('id')
    , pseudo = $('#' + id).data('pseudo')
    , date = $('#' + id).data('date')
    , token = $('#token').val()

  if (!$is_connected) {
    location.href = '/se_connecter?pour=citer&qui=' + pseudo
    return
  }

  $.getJSON('/ajax/quote.php', {id: id, hash: token}, function(data) {
    if (!data.rep) {
      alert('Erreur avec la citation : ' + data.err)
      return
    }
    var citation = ""
    if ($('#newmessage').val() && !/\n\n$/.test($('#newmessage').val())) {
      citation += "\n\n"
    }
    citation += "> '''" + pseudo + "''', " + date + " http://jvrespeed.com" + location.pathname + "#" + id + "\n"
    citation += "> \n"
    citation += "> " + $.trim(data.rep).split("\n").join("\n> ")
    citation += "\n\n"
    
    $('#newmessage').val($('#newmessage').val() + citation).focus()
  })
})

$('.m-profil').click(function () {
  window.open(this.href, "_blank", "toolbar=no,location=no,directories=no,status=no,scrollbars=yes,resizable=yes,copyhistory=no,width=520,height=570,left=" + (screen.width / 2 - 520 / 2) + ",top=" + (screen.height / 2 - 570 / 2 - 20))
  return false
})

$('#floating_newmessage').click(function() {
  if (!$is_connected) {
    location.href = '/se_connecter?pour=poster&forum=' + $forum + '&topic=' + $topic + '&slug=' + $slug
    return
  }
})

$('.meta-menu').click(function(e) {
  var id = e.target.parentNode.parentNode.parentNode.parentNode.id
  $('#' + id).toggleClass('show-menu')
})

$('.message').click(function(e) {
  if (e.target.className == 'meta-menu') {
    return
  }
  var id = this.id
  $('#' + id).removeClass('show-menu')
})

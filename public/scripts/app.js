/*** Variables ***/

var form_data
  , blacklist
  , favoritesForums = []
  , favoritesTopics = []

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
  $.get('/ajax/blacklist_add.php', {id_message: id_message})
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
  //TODO: ajax sync JVC
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
  if (!is_connected) {
    return
  }
  var remoteBlacklistLastUpdate = localStorage.remoteBlacklistLastUpdate || 0
  var now = +new Date
  if (remoteBlacklistLastUpdate + (1000 * 60 * 60) < now) {
    $.getJSON('/ajax/blacklist_get.php', function(data) {
      var remoteBlacklist = data.rep
      for (var i = 0; i < remoteBlacklist.length; i++) {
        addToBlacklist(remoteBlacklist[i])
      }
      localStorage.remoteBlacklistLastUpdate = now
    })
  }
}

function updateFavorites() {
  if (!is_connected) {
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
    favoritesForums = favoritesTopics = []
    $.each(data.rep.forums, function(index, value) {
      favoritesForums.push({
        lien: value.lien,
        titre: value.titre,
      })
    })
    $.each(data.rep.forums, function(index, value) {
      favoritesForums.push({
        lien: value.lien,
        titre: value.titre,
      })
    })
    localStorage.favoritesForums = JSON.stringify(favoritesForums)
    localStorage.favoritesTopics = JSON.stringify(favoritesTopics)
    localStorage.favoritesLastUpdate = now
  })
}

/*** App ***/

$(function() {
  updateLocalBlacklist()
  updateRemoteBlacklist()
  applyBlacklist()
  updateFavorites()
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
  $.post('/ajax/post_msg.php', params, function(data, status, xhr) {
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

$('#newmessage').focus(function(e) {
  if (!form_data) {
    $.post('/ajax/post_msg.php', {url: url}, function(data, status, xhr) {
      data = JSON.parse(data)
      form_data = data.rep
      if (form_data.fs_signature) {
        $('#captcha-container').html('<br><input class="input input-captcha" id="ccode" name="ccode" placeholder="Code"> <img src="/ajax/captcha.php?signature=' + form_data.fs_signature + '" class="captcha">')
      }
    })
  }
})

$('.meta-ignore').click(function(e, a, c) {
  var id = e.target.parentNode.parentNode.id
    , pseudo = $('#' + id).data('pseudo')

  if (!is_connected) {
    location.href = '/se_connecter?pour=ignorer&qui=' + pseudo
    return
  }

  addToBlacklist(pseudo, id)
  applyBlacklist()
})

$('.meta-unignore').click(function(e, a, c) {
  var id = e.target.parentNode.parentNode.id
    , pseudo = $('#' + id).data('pseudo')

  removeFromBlacklist(pseudo)
  applyBlacklist()
})

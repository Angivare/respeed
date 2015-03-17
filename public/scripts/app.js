/*** Variables ***/

var form_data
  , blacklist

/*** Helpers ***/

function updateBlacklist() {
  blacklist = (localStorage.blacklist || '').split(' ')
  if (!blacklist[blacklist.length - 1]) {
    blacklist.pop()
  }
}

function addToBlacklist(pseudo) {
  pseudo = pseudo.toLowerCase()
  if ($.inArray(pseudo, blacklist) >= 0) {
    return
  }
  localStorage.blacklist = pseudo + ' ' + (localStorage.blacklist || '')
  updateBlacklist()
  //TODO: ajax sync JVC
}

function removeFromBlacklist(pseudo) {
  updateBlacklist()
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

/*** App ***/

$(function() {
  updateBlacklist()
  applyBlacklist()
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
    console.log('done')
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

  addToBlacklist(pseudo)
  applyBlacklist()
})

$('.meta-unignore').click(function(e, a, c) {
  var id = e.target.parentNode.parentNode.id
    , pseudo = $('#' + id).data('pseudo')

  removeFromBlacklist(pseudo)
  applyBlacklist()
})

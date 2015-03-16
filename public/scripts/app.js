var form_data

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
        $('#captcha-container').html('<br><input class="input input-captcha" name="ccode" placeholder="Code"> <img src="/ajax/captcha.php?signature=' + form_data.fs_signature + '" class="captcha">')
      }
    })
  }
})

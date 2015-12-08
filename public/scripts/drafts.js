var draftWatcherInterval
  , lastDraftSaved

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
  var val = $('.form__textarea').val()
  if (val.trim()) {
    localStorage.setItem(getDraftName(), val)
  }
  else {
    localStorage.removeItem(getDraftName())
  }
  lastDraftSaved = getDraftName()
}

function clearDraft() {
  localStorage.removeItem(getDraftName())
}

function insertDraft() {
  var draftName = getDraftName()
    , shouldDisplayImmediately = false

  if (lastDraftSaved && lastDraftSaved == getDraftName()) {
    shouldDisplayImmediately = true
  }
  else {
    lastDraftSaved = false
  }

  if (!draftName) {
    return
  }

  var draft = localStorage.getItem(draftName)
  lastTopic = $topic
  if (draft) {
    if (!$('.form__draft').hasClass('form__draft--visible')) {
      $('.form__textarea').on('input', hideDraftMention)
    }

    if (shouldDisplayImmediately) {
      $('.form__textarea').val(draft)
    }
    else {
      $('.form__draft').addClass('form__draft--visible')
      $('.form__draft-recover').click(function() {
        $('.form__draft').removeClass('form__draft--visible')
        $('.form__textarea').focus()
        $('.form__textarea').val(draft)
      })
    }
  }
}

function hideDraftMention() {
  $('.form__textarea').off('keypress', hideDraftMention)
  $('.form__draft').removeClass('form__draft--visible')
}


instantClick.on('change', function() {
  stopDraftWatcher()
  insertDraft()
  $('.form__textarea').focus(startDraftWatcher)
})

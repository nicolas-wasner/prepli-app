// Auto-resize pour tous les textarea du site
function autoResizeTextarea(el) {
  el.style.height = 'auto';
  el.style.height = (el.scrollHeight) + 'px';
}

document.addEventListener('input', function(e) {
  if (e.target.tagName === 'TEXTAREA') {
    autoResizeTextarea(e.target);
  }
});

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('textarea').forEach(function(textarea) {
    autoResizeTextarea(textarea);
  });
}); 
// Mejora progresiva de los campos de archivo: botón para quitar la selección
// antes de subir (por si elegiste el archivo equivocado). Sin dependencias.
(function () {
  function attach(input) {
    if (input.dataset.enh) return; input.dataset.enh = '1';
    var clear = document.createElement('button');
    clear.type = 'button'; clear.className = 'file-clear'; clear.textContent = 'quitar';
    clear.hidden = true;
    clear.addEventListener('click', function () {
      input.value = ''; clear.hidden = true; input.dispatchEvent(new Event('change'));
    });
    input.insertAdjacentElement('afterend', clear);
    input.addEventListener('change', function () { clear.hidden = input.files.length === 0; });
  }
  document.querySelectorAll('input[type="file"]').forEach(attach);
})();

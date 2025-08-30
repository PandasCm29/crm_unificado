const modalEditar = document.getElementById("modalEditarRequerimiento");

// === Actualizar datos del requerimiento ===
document.getElementById('button-actualizar-datos')
  .addEventListener('click', async () => {
    const form = modalEditar.querySelector('#formEditarRequerimiento');
    const data = {};
    new FormData(form).forEach((v, k) => data[k] = v);

    try {
      const resp = await fetch('../../controller/requerimiento.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await resp.json();
      if (!json.success) throw new Error(json.message);
      Swal.fire({ icon:'success', title:'¡Actualizado!', text: json.message });
      window.renderTable();
      modalEditar.classList.add('hidden');
    } catch (err) {
      Swal.fire({ icon:'error', title:'Error', text: err.message });
    }
});

// === Guardar Nuevo (clonar + actualizar) ===
document.getElementById('button-add-new')
  .addEventListener('click', async () => {
    const form = modalEditar.querySelector('#formEditarRequerimiento');
    const data = {};
    new FormData(form).forEach((v, k) => data[k] = v);

    // Indicador para el controller
    data.clonar = true;

    try {
      const resp = await fetch('../../controller/requerimiento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      const json = await resp.json();
      if (!json.success) throw new Error(json.message);

      Swal.fire('¡Listo!', `Clonado ID ${json.newId} y original actualizado.`, 'success');
      window.renderTable();
      modalEditar.classList.add('hidden');
    } catch (err) {
      Swal.fire('Error', err.message, 'error');
    }
});
// Cerrar modal
export function cerrarModal(event) {
  if (event.target === modalEditar || event.target.closest('#button-cancel')) {
    modalEditar.classList.add('hidden');
  }
}
document.getElementById('button-cancel').addEventListener('click', async event => {cerrarModal(event)});
// === Historial dentro del modal ===
const btnAgregarHist = modalEditar.querySelector("#btnAgregarHistorial");
const textAreaHist   = modalEditar.querySelector("#textAreaHist");
const listHist       = modalEditar.querySelector("#lista-historial-status");
let editingId        = null;

// Agregar / editar comentario
btnAgregarHist?.addEventListener('click', async event => {
  event.preventDefault();
  const idconsulta = Number(modalEditar.querySelector('input[name="idconsulta"]').value);
  const statusText = textAreaHist.value.trim();
  if (!statusText) return Swal.fire({ icon:'warning', title:'Escribe un comentario' });

  const payload = { tabla:'consultas', idtabla:idconsulta, status:statusText };
  let method = 'POST';
  if (editingId) {
    method = 'PUT';
    payload.idstatus = editingId;
  }

  try {
    const resp = await fetch('../../controller/historial/crear.php', {
      method, headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload)
    });
    const json = await resp.json();
    if (!json.success) throw new Error(json.message);

    // Renderizar
    if (method==='POST') {
      textAreaHist.value = '';
      const { usuario, fecha, descripcion, idstatus } = json.data;
      const div = document.createElement('div');
      div.className = "bg-white p-4 border-l-4 border-orange-500 rounded-md shadow-sm";
      div.dataset.idstatus = idstatus;
      div.innerHTML = `
        <div class="flex justify-between items-center mb-1">
          <div class="flex items-center space-x-2">
            <p class="text-sm font-semibold text-gray-800">${usuario}</p>
            <button type="button" class="btn-edit-hist text-xs text-blue-600">✏️</button>
            <button type="button" class="btn-del-hist text-xs text-red-600">🗑️</button>
          </div>
          <span class="text-xs text-gray-500">${fecha}</span>
        </div>
        <p class="text-sm text-gray-600 whitespace-pre-line">${descripcion}</p>
      `;
      listHist.prepend(div);
      Swal.fire({ icon:'success', title:'¡Guardado!', text: json.message });
    } else {
      // EDIT
      const target = listHist.querySelector(`div[data-idstatus="${editingId}"]`);
      const ps = target.querySelectorAll('p');
      if (ps[1]) ps[1].textContent = statusText;
      Swal.fire({ icon:'success', title:'¡Actualizado!', text: json.message });
      editingId = null;
      btnAgregarHist.querySelector('span').textContent = 'Agregar Comentario al historial';
    }
  } catch (err) {
    Swal.fire({ icon:'error', title:'Error', text: err.message });
  }
});

// Delegado para editar/eliminar en histórico
listHist.addEventListener('click', e => {
  const div = e.target.closest('div[data-idstatus]');
  if (!div) return;
  const idstatus = +div.dataset.idstatus;

  if (e.target.matches('.btn-edit-hist')) {
    editingId = idstatus;
    textAreaHist.value = div.querySelector('p.text-sm.text-gray-600').textContent;
    btnAgregarHist.querySelector('span').textContent = 'Editar Comentario';
  }
  if (e.target.matches('.btn-del-hist')) {
    if (!confirm('¿Eliminar comentario?')) return;
    fetch('../../controller/historial/crear.php', {
      method:'DELETE',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ idstatus, tabla:'consultas' })
    })
    .then(r=>r.json())
    .then(j=> {
      if (!j.success) throw new Error(j.message);
      div.remove();
      Swal.fire('Eliminado', j.message, 'success');
    }).catch(err=> Swal.fire('Error', err.message, 'error'));
  }
});

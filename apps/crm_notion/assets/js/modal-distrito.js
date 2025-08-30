// assets/js/modal-distrito.js
document.addEventListener('DOMContentLoaded', () => {
  let todosDistritos = [];
  const selectDistrito          = document.getElementById('selectDistrito');

  fetch(base+'controller/distrito.php')
    .then(resp => resp.json())
    .then(data => {
      if (data.success) {        
        todosDistritos = data.distritos;
        // todosDistritos.push("Otro");
        todosDistritos.forEach(distrito => {
          const option = document.createElement("option");
          option.value = distrito;
          option.textContent = distrito;
          selectDistrito.appendChild(option);          
        });
      } else {
        Swal.fire('Error', 'No se pudieron cargar los distritos', 'error');
      }
    })
    .catch(() => {
      Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
    });

  // 2) Referencias al DOM
  const inputCodigoPostal       = document.getElementById('inputCodigoPostal');
/*   const modalDistrito           = document.getElementById('modalDistrito');
  const btnCerrarDistrito       = document.getElementById('btnCerrarDistrito');
  const listaDistritos          = document.getElementById('listaDistritos');
  const buscadorDistrito        = document.getElementById('buscadorDistrito');  */

  const linkAgregarDistrito     = document.getElementById('linkAgregarDistrito');
  const modalNuevoDistrito      = document.getElementById('modalNuevoDistrito');
  const btnCancelarNuevoDistrito= document.getElementById('btnCancelarNuevoDistrito');
  const btnGuardarNuevoDistrito = document.getElementById('btnGuardarNuevoDistrito');
  const inputNuevoDistrito      = document.getElementById('inputNuevoDistrito');

  // ————— FUNCION AUXILIAR: Inyectar opción en el <select> —————
    function agregarOpcionSelect(distrito) {
    if (!selectDistrito.querySelector(`option[value="${distrito}"]`)) {
      const opt = document.createElement('option');
      opt.value = distrito;
      opt.text  = distrito;
      // Insertar justo antes de “Otro”
      const otro = selectDistrito.querySelector('option[value="otro"]');
      selectDistrito.insertBefore(opt, otro);
    }
  }

/*   // ————— Selección «Otro» abre modal de lista —————
    selectDistrito.addEventListener('change', () => {
    if (selectDistrito.value === 'otro') {
        buscadorDistrito.value = '';
        renderizarLista(todosDistritosFiltrados());
        modalDistrito.classList.remove('hidden');
    }
    }); */

/*   function abrirModalLista() {
    buscadorDistrito.value = '';
    renderizarLista(todosDistritosFiltrados());
    modalDistrito.classList.remove('hidden');
  }

  btnCerrarDistrito.addEventListener('click', () => {
    modalDistrito.classList.add('hidden');
    selectDistrito.value = '';
  });

  buscadorDistrito.addEventListener('input', () => {
    renderizarLista(todosDistritosFiltrados(buscadorDistrito.value));
  }); */

/*   function todosDistritosFiltrados(filter = '') {
    const existentes = Array.from(selectDistrito.options)
      .map(opt => opt.value).filter(v => v && v !== 'otro');
    return todosDistritos
      .filter(d => !existentes.includes(d))
      .filter(d => d.toLowerCase().includes(filter.toLowerCase()));
  } */

/*     function renderizarLista(arr) {
    listaDistritos.innerHTML = '';
    if (arr.length === 0) {
        listaDistritos.innerHTML = '<li class="text-gray-500">No hay resultados</li>';
        return;
    }
    arr.forEach(d => {
        const li = document.createElement('li');
        li.textContent = d;
        li.className = 'cursor-pointer hover:bg-gray-100 px-3 py-2 rounded';
        li.addEventListener('click', () => {
        if (!selectDistrito.querySelector(`option[value="${d}"]`)) {
            const opt = document.createElement('option');
            opt.value = d; 
            opt.text  = d;
            const otro = selectDistrito.querySelector('option[value="otro"]');
            selectDistrito.insertBefore(opt, otro);
        }
        selectDistrito.value = d;
        modalDistrito.classList.add('hidden');
        });
        listaDistritos.appendChild(li);
    });
    } */

  // —————Agregar Nuevo Distrito —————
  linkAgregarDistrito.addEventListener('click', e => {
    e.preventDefault();
    modalNuevoDistrito.classList.remove('hidden');
  });

  btnCancelarNuevoDistrito.addEventListener('click', () => {
    modalNuevoDistrito.classList.add('hidden');
    inputNuevoDistrito.value = '';
    inputCodigoPostal.value  = '';
  });

  // ————— Guardar nuevo distrito —————
btnGuardarNuevoDistrito.addEventListener('click', () => {
  const nuevo  = inputNuevoDistrito.value.trim();
  const postal = inputCodigoPostal.value.trim();

  if (!nuevo) {
    return Swal.fire('Error','Debes ingresar un nombre','error');
  }

  fetch(base+'controller/distrito.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nombre: nuevo, codigo_postal: postal })
  })
  .then(r => r.json())
  .then(json => {
    if (!json.success) throw new Error(json.message);

    // 1) Actualiza SOLO el array en memoria
    todosDistritos.push(nuevo);

   agregarOpcionSelect(nuevo);


    // 2) Feedback y limpieza de la modal de Nuevo Distrito
    Swal.fire('¡Éxito!', `Distrito "${nuevo}" agregado`, 'success');
    modalNuevoDistrito.classList.add('hidden');
    inputNuevoDistrito.value    = '';
    inputCodigoPostal.value     = '';
  })
  .catch(err => {
    Swal.fire('Error', err.message, 'error');
  });
});
});

// assets/js/modal-distrito.js
document.addEventListener('DOMContentLoaded', function () {
  const selectDistrito = document.getElementById('selectDistrito');
  const modalDistrito = document.getElementById('modalNuevoDistrito');

  selectDistrito.addEventListener('change', function () {
    if (this.value === 'otro') {
      modalDistrito.classList.remove('hidden');
    }
  });

});

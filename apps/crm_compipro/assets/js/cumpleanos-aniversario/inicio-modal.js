import { mostrarTx, switchModal } from '../utils.js';

const meses = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

document.addEventListener("DOMContentLoaded", function () {
  let showFirstContainer =  true;
  const btnBuscar = document.getElementById("btnBuscar");
  // FIRST CONTAINER
  const firstContainer = document.getElementById('select-mes');
  const select = firstContainer.querySelector('select');
  
  // SECOND CONTAINER
  const filterContainer = document.getElementById('contenidoTabla');
  const filterBtns = filterContainer.querySelectorAll('button')
  const table = document.getElementById('tabla-cumpleaneros');
  const filasContainer = table.querySelector('tbody');
  
  // MODAL
  const modal = document.getElementById("modal-cumpleanios");
  const btnCerrar = modal?.querySelector('#btn-cerrar-modal');
  const form = modal?.querySelector('#form-cumpleanios');
  const inputFCumpleanios = form?.querySelector('input');
  const textAcciones = form?.querySelector('textarea');

  // INSTANCES
  function selectFilterBtn(btn, select = true) {
    btn?.classList.toggle('bg-orange-600', select);
    btn?.classList.toggle('selected', select);
    btn?.classList.toggle('bg-primary', !select);
  }
  function showContainer(){
    firstContainer?.classList.toggle('hidden', !showFirstContainer);
    filterContainer?.classList.toggle('hidden', showFirstContainer);
    filterContainer?.parentElement.classList.toggle('hidden', showFirstContainer);
    table?.classList.toggle('hidden', showFirstContainer);
  }
  showContainer();


  function switchModalCumple(open = false, cumple = '', acciones = '') {
    switchModal(modal, open);
    if (open) {
      textAcciones?.focus();
      textAcciones?.select();
      if (textAcciones) textAcciones.value = acciones || '';
      if (inputFCumpleanios) {
        inputFCumpleanios._flatpickr?.setDate(cumple);
      }
    }
  }

  btnCerrar?.addEventListener('click', () => switchModalCumple(false));

  async function filterByMonth(e) {
    const btn = e.target;
    // Seleccionar botón
    const btnSelected = filterContainer.querySelector('.selected');
    selectFilterBtn(btnSelected, false);
    selectFilterBtn(btn, true);
    filterBtns.forEach(btn => btn.disabled = true);

    const mes = btn.id.replace('btn', '');
    fetch('../../controller/cliente/cumpleanios-aniversario.php?mes=' + mes, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ mes: mes })
    }).then(res => res.json()).then(clientes => {
      // TABLA
      filasContainer.innerHTML = '';
      const mesNombre = meses[parseInt(mes, 10) - 1];
      clientes.forEach(cliente => {
        const fechas = [formatearFechaConMesTexto(cliente.cumpleanios), formatearFechaConMesTexto(cliente.aniversario)];
        const fechasAResaltar = fechas.filter(f => f.includes(mesNombre));
        const valores = [
          cliente.idcliente.toString(),
          cliente.razon || '',
          cliente.nombres + ' ' + cliente.apellidos,
          cliente.cargo || '',
          fechas[0] || '-',
          fechas[1] || '-',
          cliente.accionescliente || '-',
          `<div><button class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 text-xs">
                  Modificar acciones y<br>cumpleaños <i class="fas fa-edit ml-2"></i>
                </button></div>`
        ];

        const fila = document.createElement('tr');
        fila.innerHTML = mostrarTx(valores, 1, fechasAResaltar);

        const btn = fila.querySelector('button');
        if (btn) {
          btn.addEventListener('click', () => {
            switchModalCumple(true, cliente.cumpleanios, cliente.accionescliente);
            if (form) form.dataset.id = cliente.idcliente;
          });
        }

        filasContainer.appendChild(fila);
      });
      filterBtns.forEach(btn => btn.disabled = false);
    });
  }

  filterBtns.forEach(btn => btn.addEventListener('click', filterByMonth));

  btnBuscar.addEventListener("click", ()=> {
    showFirstContainer=!showFirstContainer;
    showContainer();
    const btn = filterContainer?.querySelector(`#btn${select.value}`);
    btn?.click()
  });
  form?.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const data = new FormData(form);
      const id = form.dataset.id;
      const resp = await fetch(`../../controller/cliente/actualizar-acc-cumple.php?id=${id}`, { method: 'POST', body: data });
      const json = await resp.json();
      if (json.success) {
        Swal.fire({ icon: 'success', title: '¡Guardado!', text: json.message })
          .then(() => {
            switchModalCumple(false);
            const btn = filterContainer.querySelector('.selected');
            if (btn) btn.click();
          });
      } else {
        Swal.fire({ icon: 'error', title: 'Error al guardar', text: json.message });
      }
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Error de red', text: err.message });
    }
  });

  flatpickr(".datepicker", {
    altInput: true,
    altFormat: "d/m/Y",
    dateFormat: "Y-m-d",
    allowInput: true,
    locale: {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
        longhand: ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"]
      },
      months: {
        shorthand: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
        longhand: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"]
      }
    }
  });

  function formatearFechaConMesTexto(fechaISO) {
    if (!fechaISO || typeof fechaISO !== 'string') return '';
    const [anio, mes, dia] = fechaISO.split('-');
    if (!anio || !mes || !dia) return '';
    const nombreMes = meses[parseInt(mes, 10) - 1] || '';
    return `${dia.padStart(2, '0')} de ${nombreMes}`;
  }
});

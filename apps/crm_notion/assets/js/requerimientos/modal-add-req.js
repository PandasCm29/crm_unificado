import { switchModal } from '../utils.js';

document.addEventListener("DOMContentLoaded", () => {
  //MODALES
  const modalReq = document.getElementById("modal-requerimientos");
  const btnCerrarModal = modalReq?.querySelector("#btn-cerrar-modal");
  // FORM
  const form = modalReq?.querySelector("#form-requerimientos");
  const container = form?.querySelector(".container");
  const btnModalAgregar = document.getElementById("btn-abrir-modal");

  // const tabla = document.querySelector('.tabla-mini');

  // Abrir - Cerrar
  function switchModalAgregarReq(mostrar=true){
    switchModal(modalReq, mostrar, true);
  }
  btnModalAgregar?.addEventListener("click", switchModalAgregarReq);
  btnCerrarModal?.addEventListener("click", () => switchModalAgregarReq(false));
  modalReq.addEventListener("click", switchModalAgregarReq(false));
  
  form?.addEventListener('submit', async e => {
    e.preventDefault();
    try {
      const data = new FormData(form);
      // [...data].forEach((k,v)=>console.log(k[0],"-",k[1]))
      const resp = await fetch(base+'controller/requerimientos/requerimiento.php', { method: 'POST', body: data });
      const json = await resp.json();
      if (json.success) {
          Swal.fire({ icon:'success', title:'¡Guardado!', text:json.message })
          .then(() => {
            btnCerrarModal?.click();
            // Actualizar la tabla (solo si existe la función en dashboard)
            window.renderTable?.();
          });
      } else {
          Swal.fire({ icon:'error', title:'Error al guardar', text:json.message });
      }
    } catch (err) {
      Swal.fire({ icon:'error', title:'Error de red', text:err.message });
    }
    });
});

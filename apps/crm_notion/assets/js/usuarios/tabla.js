import { mostrarTx, switchModal } from "../utils.js";
import { paginacion } from "../requerimientos/paginacion.js";

document.addEventListener("DOMContentLoaded", function () {
  const table = document.querySelector(".tabla-mini");
  const filasContainer = table.querySelector("tbody");
  const btnSearch = document.getElementById("button-search-users");
  const btnReload = document.getElementById("button-reload-search");
  const inputSearch = document.getElementById("texto-buscar");
  const modalEditar = document.getElementById("modal-editar-usuario");
  const form = modalEditar?.querySelector("#form-usuario");

  document.getElementById("estado").addEventListener("change", (e) => {
    document.getElementById("estado-label").textContent = e.target.checked
      ? "Activo"
      : "Inactivo";
  });
  async function cargarUsuarios(clear=false) {
    try {
      const { page } = paginacion.getQueryParams();

      let text = inputSearch.value.trim();
      if(clear) text="";
      //RUTA XQ AMI NO ME FUNCIONA NORMAL
      const response = await fetch(
        `${base}controller/usuarios/listar.php?page=${page}&filtro=${text}`
      );
      const data = await response.json();
      if (data.success) {
        const usuarios = Array.isArray(data)
          ? data
          : data.data.usuarios || data.data || [];
        filasContainer.innerHTML = "";
        if (!Array.isArray(usuarios)) {
          throw new Error("La respuesta no contiene un array de usuarios");
        }
        const totalRecords = data.total;
        paginacion.updatePaginationInfo(totalRecords);

        usuarios.forEach((usuario) => {
          const btnEditar = `<button 
            class="p-0 m-0 bg-transparent border-none outline-none shadow-none hover:bg-gray-100 edit-btn" 
            title="Editar">
            <svg 
                xmlns="http://www.w3.org/2000/svg" 
                class="h-5 w-5 m-1 text-blue-600 hover:text-blue-800" 
                fill="none" 
                viewBox="0 0 24 24" 
                stroke="currentColor"
            >
                <path 
                    stroke-linecap="round" 
                    stroke-linejoin="round" 
                    stroke-width="2" 
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                />
            </svg>
        </button>`;
          const divAcciones = `<div data-id="${usuario.idusuario}" class="flex flex-row justify-center space-x-2">${btnEditar}</div>`;
          const valores = [
            usuario.idusuario?.toString() || "",
            usuario.nombres + " " + usuario.apellidos || "",
            usuario.usuario || "",
            usuario.area || "",
            usuario.estado === "1"
              ? '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">Activo</span>'
              : '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs">Inactivo</span>',
            divAcciones,
          ];
          const fila = document.createElement("tr");
          fila.innerHTML = mostrarTx(valores, 1);
          filasContainer.appendChild(fila);
          const btn = fila.querySelector(".edit-btn");
          if (btn) {
            btn.addEventListener("click", () => {
              abrirModalEditar(usuario.idusuario);
            });
          }
        });
        paginacion.renderPaginationControls();
        reloaded = !data.filtering;
        if (!data.filtering) {
          Swal.fire({
            icon: "info",
            title: "Sin resultados",
            text: "Mostrando todos los usuarios",
          });
        }
      } else {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message || "No se pudieron cargar los usuarios",
        });
      }
    } catch (error) {
      console.error("Error al cargar usuarios:", error);
      filasContainer.innerHTML =
        '<tr><td colspan="6" class="text-center text-red-500 py-4">Error al cargar usuarios</td></tr>';
    }
  }

  function abrirModalEditar(usuarioId) {
    fetch(`${base}controller/usuarios/obtener.php?id=${usuarioId}`)
      .then((res) => res.json())
      .then((usuario) => {
        llenarFormulario(usuario);
        switchModal(modalEditar, true);
      });
  }
  function llenarFormulario(data) {
    idusuario = data.idusuario;
    Object.entries(data).forEach(([key, value]) => {
      const campos = form.querySelectorAll(`[name="${key}"]`);
      if (!campos.length) return;

      campos.forEach((campo) => {
        const tag = campo.tagName;
        const type = campo.type;

        if (type === "checkbox") {
          campo.checked = value === "1" || value === 1 || value === true;
          campo.value = value;
        } else if (type === "radio") {
          campo.checked = campo.value == value;
        } else if (tag === "SELECT") {
          campo.value = value;
        } else if (campo.classList.contains("datepicker") && campo._flatpickr) {
          campo._flatpickr.setDate(value, true);
        } else {
          campo.value = value;
        }
      });
    });
  }

  inputSearch?.addEventListener("input", () => {
    const text = inputSearch.value.trim();
    if (btnSearch) btnSearch.disabled = text === "";
  });
  btnSearch?.addEventListener("click", ()=>{ 
    paginacion.setCurrentPage(1);
    cargarUsuarios();
  });
  let reloaded = true;
  btnReload?.addEventListener("click", async () => {
    if (!reloaded) {
      inputSearch.value = "";
      await cargarUsuarios();
    }
    reloaded = true;
  });
  paginacion.renderFunction = ()=>cargarUsuarios(true);
  paginacion.renderFunction();
});

// ====================== FUNCIONES BASE ======================
function crearTablaConFiltros(config) {
  const {
    tablaId,
    paginacionId,
    endpoint,
    filtros,           // [{ id: "filtro-nombre", campo: "nombre" }, { id: "filtro-rol", campo: "rol" }]
    rowsPerPageId,
    renderFila,
  } = config;

  const tablaBody = document.querySelector(`#${tablaId} tbody`);
  const paginacion = document.getElementById(paginacionId);
  const rowsSelect = document.getElementById(rowsPerPageId);

  let currentPage = 1;
  let totalPages = 1;
  let rowsPerPage = parseInt(rowsSelect.value);

  function obtenerFiltros() {
    const obj = {};
    filtros.forEach(f => {
      const val = document.getElementById(f.id).value;
      if (val !== "") obj[f.campo] = val;
    });
    return obj;
  }

  async function cargarDatos() {
    const filtrosActivos = obtenerFiltros();
    const params = new URLSearchParams({ ...filtrosActivos, page: currentPage, limit: rowsPerPage });

    const res = await fetch(`${endpoint}?${params.toString()}`);
    const data = await res.json();

    tablaBody.innerHTML = "";
    data.registros.forEach(item => {
      const fila = renderFila ? renderFila(item) : crearFilaDefault(item);
      tablaBody.appendChild(fila);
    });

    totalPages = Math.ceil(data.total / rowsPerPage);
    renderizarPaginacion();
  }
  
  function crearFilaDefault(item) {
    const fila = document.createElement("tr");
    Object.values(item).forEach(val => {
      const td = document.createElement("td");
      td.textContent = val;
      fila.appendChild(td);
    });
    return fila;
  }

  function renderizarPaginacion() {
    paginacion.innerHTML = "";
    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.disabled = (i === currentPage);
      btn.addEventListener("click", () => {
        currentPage = i;
        cargarDatos();
      });
      paginacion.appendChild(btn);
    }
  }

  filtros.forEach(f => {
    document.getElementById(f.id).addEventListener("input", () => {
      currentPage = 1;
      cargarDatos();
    });
  });

  rowsSelect.addEventListener("change", () => {
    rowsPerPage = parseInt(rowsSelect.value);
    currentPage = 1;
    cargarDatos();
  });

  cargarDatos();
}

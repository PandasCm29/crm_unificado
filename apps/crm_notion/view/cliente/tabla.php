  <?php
  $encabezados = [
    "Acciones",
    "N°",
    "Usuario",
    "F. Atención",
    "Razón Comercial",
    "Razón Social",
    "Rubro",
    "Tipo de Cliente",
    "Origen",
    "Estado",
    "Estado de Atención",
    "Nombre",
    "Apellido",
    "Cargo",
    "Teléfonos",
    "RUC",
    "Email",
    "Web",
    "Dirección",
    "Dirección Empresa",
    "Obs Dirección Empresa",
    "Referencia",
    "Distrito",
    "Ciudad",
    "Cumpleaños",
    "Aniversario"
  ];

  include_once 'components/utilidades-tabla.php';
  ?>
  <div class="bg-white w-full rounded-lg shadow-md px-1 ml-12">
    <div class="w-full max-h-[75vh] bg-white rounded-lg shadow-md">
      <table class="tabla-mini w-full divide-y divide-gray-200 text-xs">
        <thead class="sticky top-0 z-20 bg-gray-800 text-white">
          <tr>
            <?php foreach ($encabezados as $index => $texto): ?>
              <th class="p-2 text-center <?= $index === 0 ? 'rounded-tl-lg' : '' ?> <?= $index === count($encabezados) - 1 ? 'rounded-tr-lg' : '' ?>">
                <?= htmlspecialchars($texto) ?>
              </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- contenido dinámico -->
        </tbody>
      </table>
    </div>

  </div>

  <div id="modalRegistrosEliminados" class="fixed inset-0 bg-gray-600 z-[1006] bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 lg:w-4/5 shadow-lg rounded-md bg-white">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 m-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Registros Eliminados
        </h3>
        <button onclick="cerrarModalEliminados()" class="text-gray-500 hover:text-gray-700">
          <svg class="h-6 w-6 m-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      <div class="max-h-[65vh] overflow-auto border rounded">
        <div class="min-w-[1200px]">
          <div class="flex items-center space-x-2 mb-4 px-4 pt-2">
            <label for="registros-por-pagina" class="text-xs text-gray-600">Mostrar:</label>
            <select id="registros-por-pagina" class="text-xs border border-gray-300 rounded px-2 py-1 bg-white">
              <option value="50">50</option>
              <option value="100">100</option>
              <option value="150">150</option>
              <option value="200">200</option>
            </select>
            <span class="text-xs text-gray-600">por página</span>
            <div>
              <label class="block text-xs font-medium text-gray-700 mb-0.5">Buscar Por:</label>
              <div class="flex">
                <select id="del-filtro-por"
                  class="w-1/2 border text-xs border-gray-300 rounded-l px-1 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                  <option class="text-xs" value="todos">Todos</option>
                  <option class="text-xs" value="num_cliente">ID / Numero cliente</option>
                  <option class="text-xs" value="empresa">Razón Comercial "Empresa"</option>
                  <option class="text-xs" value="razon_social">Razón Social</option>
                  <option class="text-xs" value="nombres">Nombres</option>
                  <option class="text-xs" value="apellidos">Apellidos</option>
                  <option class="text-xs" value="usuarios">Usuarios</option>
                  <option class="text-xs" value="rubro">Rubro</option>
                  <option class="text-xs" value="ruc">RUC</option>
                  <option class="text-xs" value="telefono">Teléfono</option>
                  <option class="text-xs" value="celular">Celular</option>
                  <option class="text-xs" value="fecha_atencion">Fecha de Atención</option>
                  <option class="text-xs" value="direccion_cliente">Dirección Cliente</option>
                  <option class="text-xs" value="direccion_empresa">Dirección Empresa</option>
                  <option class="text-xs" value="emails">Emails</option>
                  <option class="text-xs" value="web">Web</option>
                </select>

                <div class="relative w-full">
                  <input id="del-texto-buscar" type="text" placeholder="Buscar"
                    class="w-full border text-xs border-gray-300 border-l-0 rounded-r px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary">
                  <i class="fas fa-times absolute right-2 top-1 text-xs cursor-pointer" onclick="clearInputBPEliminados()"></i>
                </div>
              </div>
            </div>
          </div>

          <table id="tabla-eliminados" class="tabla-mini min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-800 text-white">
              <tr>
                <th class="px-4 py-2 text-xs rounded-tl-lg">N°</th>
                <th class="px-4 py-2 text-xs">Usuario</th>
                <th class="px-4 py-2 text-xs">F. Atención</th>
                <th class="px-4 py-2 text-xs">Razón Comercial</th>
                <th class="px-4 py-2 text-xs">Razón Social</th>
                <th class="px-4 py-2 text-xs">Rubro</th>
                <th class="px-4 py-2 text-xs">Tipo de Cliente</th>
                <th class="px-4 py-2 text-xs">Origen</th>
                <th class="px-4 py-2 text-xs">Estado</th>
                <th class="px-4 py-2 text-xs">Estado de Atención</th>
                <th class="px-4 py-2 text-xs">Nombre</th>
                <th class="px-4 py-2 text-xs">Apellido</th>
                <th class="px-4 py-2 text-xs">Cargo</th>
                <th class="px-4 py-2 text-xs">Teléfonos</th>
                <th class="px-4 py-2 text-xs">RUC</th>
                <th class="px-4 py-2 text-xs">Email</th>
                <th class="px-4 py-2 text-xs">Web</th>
                <th class="px-4 py-2 text-xs">Dirección</th>
                <th class="px-4 py-2 text-xs">Dirección Empresa</th>
                <th class="px-4 py-2 text-xs">Obs Dirección Empresa</th>
                <th class="px-4 py-2 text-xs">Referencia</th>
                <th class="px-4 py-2 text-xs">Distrito</th>
                <th class="px-4 py-2 text-xs">Ciudad</th>
                <th class="px-4 py-2 text-xs">Cumpleaños</th>
                <th class="px-4 py-2 text-xs rounded-tr-lg">Aniversario</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tabla-eliminados-body">
            </tbody>
          </table>

        </div>
      </div>
      <div class="flex justify-between items-center mt-4 px-4 pb-2">
        <div class="text-xs text-gray-600">
          Mostrando <span id="paginacion-info">0 - 0 de 0</span> registros
        </div>
        <div class="flex items-center space-x-2">
          <button id="btn-anterior" class="px-3 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Anterior
          </button>
          <span class="text-xs text-gray-600">
            Página <span id="pagina-actual">1</span> de <span id="total-paginas">1</span>
          </span>
          <button id="btn-siguiente" class="px-3 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
            Siguiente
          </button>
        </div>
      </div>
    </div>
  </div>

  <div id="modalEstado" class="fixed inset-0 z-50  items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md relative px-4 py-4">
      <button class="btn-cerrar-vm absolute top-2 right-2 text-gray-500 hover:text-red-500 bg-transparent text-lg">×</button>
      <h2 class="text-sm font-semibold text-gray-800 mb-3">Estado Completo</h2>
      <p id="estado-contenido" class="text-xs text-gray-700"></p>
      <div class="text-right mt-4">
        <button class="btn-cerrar-vm text-xs px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">Cerrar</button>
      </div>
    </div>
  </div>
  <script src="assets/js/listar-clientes/tabla.js"></script>
  <script>
    const modalEstado = document.getElementById('modalEstado');
    // Mostrar el modal de estado completo
    function abrirModalEstado(contenido) {
      const contenidoElemento = document.getElementById('estado-contenido');
      if (!modalEstado || !contenidoElemento) {
        console.error('Modal o elemento de contenido no encontrado');
        return;
      }
      contenidoElemento.textContent = contenido;
      modalEstado.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }

    // Cerrar el modal de estado
    function cerrarModalEstado(event) {
      if (event) event.stopPropagation();
      if (!modalEstado) return;
      modalEstado.classList.add('hidden');
      document.body.style.overflow = 'auto';
    }

    const colIndexEliminados = {
      todos: null,
      num_cliente: 1,
      usuarios: 2,
      fecha_atencion: 3,
      empresa: 4,
      razon_social: 5,
      rubro: 6,
      tipo_cliente: 7,
      origen: 8,
      estado_atencion: 10,
      nombres: 11,
      apellidos: 12,
      cargo: 13,
      telefono: 14,
      celular: 14,
      ruc: 15,
      emails: 16,
      web: 17,
      direccion_cliente: 18,
      direccion_empresa: 19,
      obs_direccion: 20,
      referencia: 21,
      distrito: 22,
      ciudad: 23,
      cumpleanios: 24,
      aniversario: 25,
    };
    const tablaBodyEliminados = document.getElementById("tabla-eliminados");
    const delSelLocal = document.getElementById("del-filtro-por");
    const delTxtLocal = document.getElementById("del-texto-buscar");
    const selectRegistros = document.getElementById('registros-por-pagina');
    const filtrarLocal = () => {
      // Validar entradas
      if (!tablaBodyEliminados || !delTxtLocal || !delSelLocal) {
        console.error("Elementos necesarios no están definidos");
        return;
      }
      const f = delTxtLocal.value.toLowerCase().trim();
      if (!f) {
        // Si no hay texto de búsqueda, mostrar todas las filas y limpiar resaltado
        Array.from(tablaBodyEliminados.rows).forEach((row, counter) => {
          if (counter === 0) return; // Saltar la primera fila (encabezado)
          Array.from(row.cells).forEach((cell) => {
            cell.style.backgroundColor = ""; // Limpiar fondo
          });
          row.style.display = ""; // Mostrar todas las filas
        });
        return;
      };
      // Obtener el índice de la columna, o null si no está definido
      const idx = colIndexEliminados[delSelLocal.value] !== null ?
        colIndexEliminados[delSelLocal.value] - 1 :
        null;
      Array.from(tablaBodyEliminados.rows).forEach((r, counter) => {
        if (counter === 0) return; // Saltar la primera fila (encabezado)
        let evaluacion = false;
        Array.from(r.cells).forEach((cell) => {
          cell.style.backgroundColor = ""; // Restablecer fondo
        });
        if (idx == null) {
          Array.from(r.cells).forEach((cell) => {
            if (cell.textContent.toLowerCase().includes(f)) {
              evaluacion = true; // Encontró coincidencia, marcar para mostrar la fila
              cell.style.backgroundColor = "#FFFF00";
            }
          });
        } else {
          const cell = r.cells[idx];
          const cellText = (cell?.textContent || "").toLowerCase();
          if (cellText.includes(f)) {
            evaluacion = true; // Encontró coincidencia
          }
        }
        r.style.display = evaluacion ? "" : "none";
      });
    };
    // FILTRAR POR
    delTxtLocal?.addEventListener("input", filtrarLocal);
    delSelLocal?.addEventListener("change", filtrarLocal);

    window.clearInputBPEliminados = () => {
      const i = document.getElementById("del-texto-buscar");
      if (i) {
        const text = i.value;
        i.focus();
        if (!text) return;
        i.value = "";
        filtrarLocal();
      }
    };

    function cerrarModalEliminados() {
      const modal = document.getElementById('modalRegistrosEliminados');
      if (!modal) return;
      modal.classList.add('hidden');
      document.body.style.overflow = '';
    }
    let paginaActualEliminados = 1;
    let registrosPorPagina = 50;
    let totalRegistrosEliminados = 0;
    let clientesEliminadosData = [];

    function cargarClientesEliminados(pagina = 1) {
      const tbody = document.getElementById('tabla-eliminados-body');
      tbody.innerHTML = '<tr><td colspan="27" class="text-xs text-center py-4">Cargando registros eliminados...</td></tr>';
      const params = new URLSearchParams({
        action: 'get_eliminados',
        page: pagina,
        limit: registrosPorPagina
      });
      delTxtLocal.disabled = true;
      delSelLocal.disabled = true;
      fetch('controller/cliente.php?' + params.toString())
        .then(response => {
          if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
          }
          return response.json();
        })
        .then(data => {
          if (!data.success) {
            throw new Error(data.message || 'Error al obtener datos');
          }
          clientesEliminadosData = data.clientes || [];
          totalRegistrosEliminados = data.total || 0;
          paginaActualEliminados = pagina;
          renderizarTablaEliminados();
          actualizarPaginacionEliminados();
          if (selectRegistros && selectRegistros.value != registrosPorPagina) {
            selectRegistros.value = registrosPorPagina;
          }
        })
        .catch(error => {
          console.error('Error al cargar clientes eliminados:', error);
          tbody.innerHTML =
            '<tr><td colspan="27" class="text-center py-4 text-red-500">Error al cargar los datos: ' + error.message + '</td></tr>';
        }).finally(() => {
          delTxtLocal.disabled = false;
          delSelLocal.disabled = false;
        });

    }

    function renderizarTablaEliminados() {
      const tbody = document.getElementById('tabla-eliminados-body');
      tbody.innerHTML = '';
      if (!clientesEliminadosData || clientesEliminadosData.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = '<td colspan="27" class="text-xs text-center py-4 text-gray-500">No hay registros eliminados</td>';
        tbody.appendChild(tr);
        return;
      }
      clientesEliminadosData.forEach(cliente => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        let telefonos = '';
        if (cliente.telefono) telefonos += cliente.telefono + '\n';
        for (let i = 1; i <= 4; i++) {
          const celKey = `celular${i}`;
          if (cliente[celKey]) {
            telefonos += cliente[celKey] + (i === 4 ? '' : '\n');
          }
        }
        telefonos = telefonos.trim();
        let tipo_cliente = "";
        if (cliente.perfil) {
          try {
            const perfilObj = JSON.parse(cliente.perfil);
            tipo_cliente = perfilObj['TIPO DE CLIENTE'] || "";
          } catch (e) {
            const texto = cliente.perfil.toLowerCase();
            const valores_validos = ["potencial", "frecuentes", "ocasionales", "tercerizadores",
              "prospecto", "no potencial", "mal cliente"
            ];
            for (const valor of valores_validos) {
              if (texto.includes(valor)) {
                tipo_cliente = valor.charAt(0).toUpperCase() + valor.slice(1);
                break;
              }
            }
          }
        }
        // Truncate estado_cliente
        const max_length = 50;
        const estado_cliente = cliente.estado_cliente || '';
        const estado_truncado = estado_cliente.length > max_length ?
          estado_cliente.substring(0, max_length) + '...' :
          estado_cliente;
        const estado_html = estado_cliente.length > max_length ?
          `${estado_truncado}<br><span class="ver-mas-estado text-blue-600 cursor-pointer hover:underline" data-estado="${estado_cliente.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}">Ver más</span>` :
          estado_cliente;
        tr.innerHTML = `
            <td class="px-4 py-2 text-xs">${cliente.idcliente}</td>
            <td class="px-4 py-2 text-xs">${cliente.usuario || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.fatencion || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.empresa || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.razon || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.rubro || ''}</td>
            <td class="px-4 py-2 text-xs">${tipo_cliente}</td>
            <td class="px-4 py-2 text-xs">${cliente.origen_nombre || ''}</td>
            <td class="px-4 py-2 text-xs">${estado_html}</td>
            <td class="px-4 py-2 text-xs">${cliente.estado_atencion || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.nombres || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.apellidos || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.cargo || ''}</td>
            <td class="px-4 py-2 text-xs whitespace-pre-line">${telefonos}</td>
            <td class="px-4 py-2 text-xs">${cliente.ruc || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.email || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.web || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.direccion || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.direccion2 || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.obsdireccion || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.referencia || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.distrito || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.ciudad || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.cumpleanios || ''}</td>
            <td class="px-4 py-2 text-xs">${cliente.aniversario || ''}</td>`;
        tbody.appendChild(tr);
      });
      filtrarLocal();
    }

    function actualizarPaginacionEliminados() {
      const totalPaginas = Math.ceil(totalRegistrosEliminados / registrosPorPagina);
      const inicio = totalRegistrosEliminados > 0 ? ((paginaActualEliminados - 1) * registrosPorPagina) + 1 : 0;
      const fin = Math.min(paginaActualEliminados * registrosPorPagina, totalRegistrosEliminados);
      const paginacionInfo = document.getElementById('paginacion-info');
      const paginaActualEl = document.getElementById('pagina-actual');
      const totalPaginasEl = document.getElementById('total-paginas');
      const btnAnterior = document.getElementById('btn-anterior');
      const btnSiguiente = document.getElementById('btn-siguiente');
      if (paginacionInfo) {
        paginacionInfo.textContent = `${inicio} - ${fin} de ${totalRegistrosEliminados}`;
      }
      if (paginaActualEl) {
        paginaActualEl.textContent = paginaActualEliminados;
      }
      if (totalPaginasEl) {
        totalPaginasEl.textContent = totalPaginas || 1;
      }
      if (btnAnterior) {
        btnAnterior.disabled = paginaActualEliminados === 1;
      }
      if (btnSiguiente) {
        btnSiguiente.disabled = paginaActualEliminados === totalPaginas || totalPaginas === 0;
      }
    }
    document.addEventListener('DOMContentLoaded', function() {
      document.body.addEventListener('click', function(e) {
        if (e.target.id === 'btn-anterior' && !e.target.disabled) {
          if (paginaActualEliminados > 1) {
            cargarClientesEliminados(paginaActualEliminados - 1);
          }
        }
        if (e.target.id === 'btn-siguiente' && !e.target.disabled) {
          const totalPaginas = Math.ceil(totalRegistrosEliminados / registrosPorPagina);
          if (paginaActualEliminados < totalPaginas) {
            cargarClientesEliminados(paginaActualEliminados + 1);
          }
        }
      });
      document.body.addEventListener('change', function(e) {
        if (e.target.id === 'registros-por-pagina') {
          const nuevoValor = parseInt(e.target.value);
          if (nuevoValor && nuevoValor !== registrosPorPagina) {
            registrosPorPagina = nuevoValor;
            paginaActualEliminados = 1;
            cargarClientesEliminados(1);
          }
        }
      });
      document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('ver-mas-estado')) {
          const estadoCompleto = e.target.getAttribute('data-estado')
            .replace(/&quot;/g, '"')
            .replace(/&#39;/g, "'");
          abrirModalEstado(estadoCompleto);
        }
      });
    });

    function abrirModalEliminados() {
      const modal = document.getElementById('modalRegistrosEliminados');
      if (!modal) {
        console.error('modalRegistrosEliminados no encontrado');
        return;
      }
      modal.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
      paginaActualEliminados = 1;
      registrosPorPagina = 50; //VALOR POR DEFECTO
      setTimeout(() => {
        if (selectRegistros) {
          selectRegistros.value = registrosPorPagina;
        }
        cargarClientesEliminados(1);
      }, 50);
    }
  </script>

  <style>
    #modalRegistrosEliminados .tabla-mini th,
    #modalRegistrosEliminados .tabla-mini td {
      padding: 0.5rem;
      text-align: left;
      white-space: nowrap;
    }

    #modalRegistrosEliminados .tabla-mini th:first-child,
    #modalRegistrosEliminados .tabla-mini td:first-child {
      width: 80px;
    }

    /* Ensure table cells don't wrap unnecessarily */
    .tabla-mini td {
      max-width: 200px;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Style for the estado modal */
    #modalEstado {
      z-index: 1010;
      /* Higher than other elements to ensure it stays on top */
    }

    #modalEstado p {
      white-space: pre-wrap;
      /* Preserve line breaks in the estado text */
      max-height: 400px;
      overflow-y: auto;
      padding: 0.5rem;
      border: 1px solid #e5e7eb;
      border-radius: 4px;
    }
  </style>
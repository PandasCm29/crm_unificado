function conectarWebSocket(onMensajeRecibido) {
  let socket;
  const segundos = 5;
  let contadorError = 0;
  const intentosVisibles = 5;

  const areasPermitidas = ["Administracion", "Logistica", "Comercial", "Programacion", "Soporte"];
  function manejarMensaje(data) {
    try {
      onMensajeRecibido?.(data);
    } catch (err) {
      console.error("Mensaje inválido:", err);
    }
  }
  function crearConexion() {
    const usuario = currentUser;
    const areaUsuario = usuario.area || "Desconocido";

    if (!areasPermitidas.includes(areaUsuario)) {
      console.warn(`🔒 Área "${areaUsuario}" no tiene acceso a WebSocket. Conexión cancelada.`);
      return;
    }
    const hostname = window.location.hostname;
    window.urlWss = `https://${hostname}:8445`;
    socket = io(urlWss, {
      transports: ["websocket"],
      path: "/ws-notion/socket.io",
      reconnection: false,
    });
    socket.on("connect", () => {
      socket.emit("identificar", {
        tipo: areaUsuario,
        usuario: usuario.user,
      });
      Swal.fire({
        toast: true,
        position: "bottom-start",
        icon: "success",
        title: "Conectado al servidor Socket.IO",
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
      });
      contadorError = 0;
    });

    socket.on("clientes-actualizados", manejarMensaje);

    socket.on("disconnect", () => {
      setTimeout(() => {
        crearConexion();
      }, segundos * 1000);
    });

    socket.on("connect_error", (err) => {
      contadorError++;
      if (contadorError === 1) {
        Swal.fire({
          icon: "error",
          title: "Sin conexión en tiempo real para las notificaciones",
          text: "No se pudo conectar al servidor WebSocket. Por favor, contacta con el administrador para activarlo.",
          confirmButtonText: "Entendido",
        });
      } else if (contadorError < intentosVisibles) {
        Swal.fire({
          toast: true,
          position: "bottom-start",
          icon: "error",
          title: "Error de conexión WebSocket",
          text: `Reintentando en ${segundos} segundos...`,
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        });
      }
      setTimeout(() => {
        if (socket) socket.disconnect();
        crearConexion();
      }, segundos * 1000);
    });
  }
  crearConexion();
  return socket;
}

function conectarSincronizacion(){
  return true;
}

function verificarRol() {
    const usuario = JSON.parse(sessionStorage.getItem('usuario'));
    const rol = usuario ? usuario.rol : 'invitado';
    
    // Ocultar todos los elementos con data-rol primero
    document.querySelectorAll('[data-rol]').forEach(el => {
        el.style.display = 'none';
    });
    
    // Mostrar elementos según rol
    switch(rol) {
        case 'admin':
            document.querySelectorAll('[data-rol]').forEach(el => el.style.display = 'block');
            break;
        case 'editor':
            document.querySelectorAll('[data-rol="editor"], [data-rol="usuario"], [data-rol="invitado"]').forEach(el => el.style.display = 'block');
            break;
        case 'usuario':
        case 'trabajador': // Compatibilidad con ambos nombres
            document.querySelectorAll('[data-rol="usuario"], [data-rol="invitado"]').forEach(el => el.style.display = 'block');
            break;
        case 'invitado':
            document.querySelectorAll('[data-rol="invitado"]').forEach(el => el.style.display = 'block');
            break;
    }
    
    // Actualizar UI con datos del usuario
    if (usuario) {
        const nombreElements = document.querySelectorAll('#nombre-usuario, #nombre');
        nombreElements.forEach(el => {
            el.textContent = usuario.nombre;
        });
        
        const rolElements = document.querySelectorAll('#rol-usuario');
        rolElements.forEach(el => {
            el.textContent = usuario.rol;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    verificarRol();
    
    // Verificar si hay un parámetro de logout en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('logout')) {
        sessionStorage.removeItem('usuario');
    }
});
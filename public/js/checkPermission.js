document.addEventListener("DOMContentLoaded", async function () {
    async function checkPermission(permissionName) {
		try {
			const res = await fetch(`api/check_permission.php?permission=${encodeURIComponent(permissionName)}`);
			const data = await res.json();

			if (data.success) {
				return data.has_permission;
			} else {
				console.error("Error fetching permission:", data.message);
				return false;
			}
		} catch (error) {
			console.error("Error checking permission:", error);
			return false;
		}
	}
	
	// Definir los permisos jerárquicos en orden descendente (más poderoso primero)
    async function getPermissionHierarchy() {
        try {
            const res = await fetch('api/get_permission_hierarchy.php');
            const data = await res.json();

            if (data.success) {
                return data.permissions;
            } else {
                console.error("Error fetching permissions hierarchy:", data.message);
                return [];
            }
        } catch (error) {
            console.error("Error fetching permissions hierarchy:", error);
            return [];
        }
    }

    const permissionHierarchy = await getPermissionHierarchy();
    console.log("Jerarquía de permisos:", permissionHierarchy);

    let grantedPermission = null;

    for (let permission of permissionHierarchy) {
        const hasPermission = await checkPermission(permission);
        console.log(`${permission}: ${hasPermission}`);
        if (hasPermission) {
            grantedPermission = permission;
            break; // Salimos con el permiso más alto concedido
        }
    }

	// const subscButton = document.getElementById("subsc-button");
	// const editCompButton = document.getElementById("edit-comp-button");
	
    // Ahora simular un "switch" usando if-else:
    if (grantedPermission === 'manage_all') {
        console.log("El usuario tiene acceso total");
        // Aquí habilitas TODO
        // ej: subscButton.disabled = false;
    } else if (grantedPermission === 'manage_intern_admin') {
        console.log("El usuario tiene acceso de administrador interno");
        // Habilita lo que este permiso permite
        // ej: subscButton.disabled = false; 
        // editCompButton.disabled = false;
    } else if (grantedPermission === 'manage_users') {
        console.log("El usuario puede gestionar usuarios");
        // Habilita solo lo permitido a este nivel
    } else {
        console.log("El usuario no tiene permisos para realizar estas acciones");
        // Deshabilita todo
    }

});
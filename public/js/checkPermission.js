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

	const subscButton = document.getElementById("subsc-button");
	const editCompButton = document.getElementById("edit-comp-button");

    const hasManageAllPermission = await checkPermission('manage_all');
	console.log(hasManageAllPermission);
    if (!hasManageAllPermission) {
		if (subscButton) {
			subscButton.disabled = true;
			subscButton.title = "You don't have permission to delete data.";
			subscButton.classList.add('button-ghost');
		}

		if (editCompButton) {
			editCompButton.disabled = true;
			editCompButton.title = "You don't have permission to edit data.";
			editCompButton.classList.add('button-ghost');
		}
    }
});
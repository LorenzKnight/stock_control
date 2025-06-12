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

	async function checkCompanyId() {
		try {
			const res = await fetch('api/get_my_info.php');
			const data = await res.json();

			if (data.success) {
				return data.data.company_id == null;
			} else {
				console.error("Error checking company ID:", data.message);
				return false;
			}
		} catch (error) {
			console.error("Error checking company ID:", error);
			return false;
		}
	}

	const isLinkedToCompany = await checkCompanyId();

    const permissionHierarchy = await getPermissionHierarchy();
    // console.log("Jerarquía de permisos:", permissionHierarchy);

    let grantedPermission = null;

    for (let permission of permissionHierarchy) {
        const hasPermission = await checkPermission(permission);
        // console.log(`${permission}: ${hasPermission}`);
        if (hasPermission) {
            grantedPermission = permission;
            break; // Salimos con el permiso más alto concedido
        }
    }

	const subscButton = document.getElementById("subsc-button");
	const editCompButton = document.getElementById("edit-comp-button");
	const addMembersButton = document.getElementById("add-members-button");

	const cardMenuBtn = document.getElementsByClassName("card-menu");

	const saleMenuBtn = document.getElementsByClassName("sale-menu");
	
	const addSaleBtn = document.getElementById("add-sale-btn");
	const editSaleBtn = document.getElementById("editSaleBtn");
	const deleteSaleBtn = document.getElementById("deleteSaleBtn");

	const productMenuBtn = document.getElementsByClassName("product-menu");

	const editProductBtn = document.getElementById("editProductBtn");
	const deleteProductBtn = document.getElementById("deleteProductBtn");

	const addProductBtn = document.getElementById("add-product-btn");
	const addCategoryBtn = document.getElementById("add-category-btn");

	const addCustomersButton = document.getElementById("add-customers-button");

	const customersMenuBtn = document.getElementsByClassName("customers-menu");
	
	const editCustomerBtn = document.getElementById("editCustomerBtn");
	const deleteCustomerBtn = document.getElementById("deleteCustomerBtn");

	const addPaymentsBtn = document.getElementById("add-payments-btn");

	const paymentsMenuBtn = document.getElementsByClassName("payments-menu");

	const editPaymentBtn = document.getElementById("editPaymentBtn");
	const deletePaymentBtn = document.getElementById("deletePaymentBtn");

	const salesSite = document.getElementById("sales-site");
	const paymentsSite = document.getElementById("payments-site");
	const adminSite = document.getElementById("admin-site");

	const elements = document.getElementsByClassName("isNotLinkedToCompany");

	if (isLinkedToCompany) {
		console.log("El usuario no está vinculado a una empresa.");

		if (addMembersButton) {
			addMembersButton.disabled = true;
			addMembersButton.title = "You don't have permission to add members.";
			addMembersButton.classList.add('button-ghost');
		}

		if (addSaleBtn) {
			addSaleBtn.disabled = true;
			addSaleBtn.title = "You don't have permission to add sales.";
			addSaleBtn.classList.add('button-ghost');
		}

		if (addProductBtn) {
			addProductBtn.disabled = true;
			addProductBtn.title = "You don't have permission to add products.";
			addProductBtn.classList.add('button-ghost');
		}

		if (addCategoryBtn) {
			addCategoryBtn.disabled = true;
			addCategoryBtn.title = "You don't have permission to add categories.";
			addCategoryBtn.classList.add('button-ghost');
		}

		if (addCustomersButton) {
			addCustomersButton.disabled = true;
			addCustomersButton.title = "You don't have permission to add customers.";
			addCustomersButton.classList.add('button-ghost');
		}

		if (addPaymentsBtn) {
			addPaymentsBtn.disabled = true;
			addPaymentsBtn.title = "You don't have permission to add payments.";
			addPaymentsBtn.classList.add('button-ghost');
		}
	} else {
		for (const el of elements) {
            el.style.display = "none";
        }
	}

    // Ahora simular un "switch" usando if-else:
    if (grantedPermission === 'manage_all') {
        console.log("El usuario tiene acceso total");
        // Aquí habilitas TODO
    } else if (grantedPermission === 'manage_intern_admin') {
        console.log("El usuario tiene acceso de administrador interno");
        // Habilita lo que este permiso permite
	} else if (grantedPermission === 'export_reports') {
        console.log("El usuario puede exportar reportes");
        // Habilita solo lo permitido a este nivel
		// if (salesSite) {
		// 	salesSite.style.display = "none";
		// }
		// if (paymentsSite) {
		// 	paymentsSite.style.display = "none";
		// }
		// if (adminSite) {
		// 	adminSite.style.display = "none";
		// }

		// if (subscButton) {
        //     subscButton.disabled = true;
        //     subscButton.title = "You don't have permission to delete data.";
        //     subscButton.classList.add('button-ghost');
        // }
        // if (editCompButton) {
        //     editCompButton.disabled = true;
        //     editCompButton.title = "You don't have permission to edit data.";
        //     editCompButton.classList.add('button-ghost');
        // }

		// if (addMembersButton) {
		// 	addMembersButton.disabled = true;
		// 	addMembersButton.title = "You don't have permission to add members.";
		// 	addMembersButton.classList.add('button-ghost');
		// }

		// if (cardMenuBtn) {
		// 	for (let btn of cardMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (saleMenuBtn) {
		// 	for (let btn of saleMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (addSaleBtn) {
		// 	addSaleBtn.disabled = true;
		// 	addSaleBtn.title = "You don't have permission to add sales.";
		// 	addSaleBtn.classList.add('button-ghost');
		// }

		// if (editSaleBtn) {
		// 	editSaleBtn.disabled = true;
		// 	editSaleBtn.title = "You don't have permission to edit sales.";
		// 	editSaleBtn.classList.add('button-ghost');
		// }

		// if (deleteSaleBtn) {
		// 	deleteSaleBtn.disabled = true;
		// 	deleteSaleBtn.title = "You don't have permission to delete sales.";
		// 	deleteSaleBtn.classList.add('button-ghost');
		// }

		// if (productMenuBtn) {
		// 	for (let btn of productMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editProductBtn) {
		// 	editProductBtn.disabled = true;
		// 	editProductBtn.title = "You don't have permission to edit products.";
		// 	editProductBtn.classList.add('button-ghost');
		// }

		// if (deleteProductBtn) {
		// 	deleteProductBtn.disabled = true;
		// 	deleteProductBtn.title = "You don't have permission to delete products.";
		// 	deleteProductBtn.classList.add('button-ghost');
		// }

		// if (addProductBtn) {
		// 	addProductBtn.disabled = true;
		// 	addProductBtn.title = "You don't have permission to add products.";
		// 	addProductBtn.classList.add('button-ghost');
		// }

		// if (addCategoryBtn) {
		// 	addCategoryBtn.disabled = true;
		// 	addCategoryBtn.title = "You don't have permission to add categories.";
		// 	addCategoryBtn.classList.add('button-ghost');
		// }

		// if (addCustomersButton) {
		// 	addCustomersButton.disabled = true;
		// 	addCustomersButton.title = "You don't have permission to add customers.";
		// 	addCustomersButton.classList.add('button-ghost');
		// }

		// if (customersMenuBtn) {
		// 	for (let btn of customersMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editCustomerBtn) {
		// 	editCustomerBtn.disabled = true;
		// 	editCustomerBtn.title = "You don't have permission to edit customers.";
		// 	editCustomerBtn.classList.add('button-ghost');
		// }

		// if (deleteCustomerBtn) {
		// 	deleteCustomerBtn.disabled = true;
		// 	deleteCustomerBtn.title = "You don't have permission to delete customers.";
		// 	deleteCustomerBtn.classList.add('button-ghost');
		// }

		// if (addPaymentsBtn) {
		// 	addPaymentsBtn.disabled = true;
		// 	addPaymentsBtn.title = "You don't have permission to add payments.";
		// 	addPaymentsBtn.classList.add('button-ghost');
		// }

		// if (paymentsMenuBtn) {
		// 	for (let btn of paymentsMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editPaymentBtn) {
		// 	editPaymentBtn.disabled = true;
		// 	editPaymentBtn.title = "You don't have permission to edit payments.";
		// 	editPaymentBtn.classList.add('button-ghost');
		// }

		// if (deletePaymentBtn) {
		// 	deletePaymentBtn.disabled = true;
		// 	deletePaymentBtn.title = "You don't have permission to delete payments.";
		// 	deletePaymentBtn.classList.add('button-ghost');
		// }
	} else if (grantedPermission === 'delete_data') {
        console.log("El usuario puede eliminar datos");
        // Habilita solo lo permitido a este nivel
		// if (salesSite) {
		// 	salesSite.style.display = "none";
		// }
		// if (paymentsSite) {
		// 	paymentsSite.style.display = "none";
		// }
		// if (adminSite) {
		// 	adminSite.style.display = "none";
		// }

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

		// if (addMembersButton) {
		// 	addMembersButton.disabled = true;
		// 	addMembersButton.title = "You don't have permission to add members.";
		// 	addMembersButton.classList.add('button-ghost');
		// }

		// if (cardMenuBtn) {
		// 	for (let btn of cardMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (saleMenuBtn) {
		// 	for (let btn of saleMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (addSaleBtn) {
		// 	addSaleBtn.disabled = true;
		// 	addSaleBtn.title = "You don't have permission to add sales.";
		// 	addSaleBtn.classList.add('button-ghost');
		// }

		// if (editSaleBtn) {
		// 	editSaleBtn.disabled = true;
		// 	editSaleBtn.title = "You don't have permission to edit sales.";
		// 	editSaleBtn.classList.add('button-ghost');
		// }

		if (deleteSaleBtn) {
			deleteSaleBtn.disabled = true;
			deleteSaleBtn.title = "You don't have permission to delete sales.";
			deleteSaleBtn.classList.add('button-ghost');
		}

		// if (productMenuBtn) {
		// 	for (let btn of productMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editProductBtn) {
		// 	editProductBtn.disabled = true;
		// 	editProductBtn.title = "You don't have permission to edit products.";
		// 	editProductBtn.classList.add('button-ghost');
		// }

		if (deleteProductBtn) {
			deleteProductBtn.disabled = true;
			deleteProductBtn.title = "You don't have permission to delete products.";
			deleteProductBtn.classList.add('button-ghost');
		}

		// if (addProductBtn) {
		// 	addProductBtn.disabled = true;
		// 	addProductBtn.title = "You don't have permission to add products.";
		// 	addProductBtn.classList.add('button-ghost');
		// }

		// if (addCategoryBtn) {
		// 	addCategoryBtn.disabled = true;
		// 	addCategoryBtn.title = "You don't have permission to add categories.";
		// 	addCategoryBtn.classList.add('button-ghost');
		// }

		// if (addCustomersButton) {
		// 	addCustomersButton.disabled = true;
		// 	addCustomersButton.title = "You don't have permission to add customers.";
		// 	addCustomersButton.classList.add('button-ghost');
		// }

		// if (customersMenuBtn) {
		// 	for (let btn of customersMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editCustomerBtn) {
		// 	editCustomerBtn.disabled = true;
		// 	editCustomerBtn.title = "You don't have permission to edit customers.";
		// 	editCustomerBtn.classList.add('button-ghost');
		// }

		if (deleteCustomerBtn) {
			deleteCustomerBtn.disabled = true;
			deleteCustomerBtn.title = "You don't have permission to delete customers.";
			deleteCustomerBtn.classList.add('button-ghost');
		}

		// if (addPaymentsBtn) {
		// 	addPaymentsBtn.disabled = true;
		// 	addPaymentsBtn.title = "You don't have permission to add payments.";
		// 	addPaymentsBtn.classList.add('button-ghost');
		// }

		// if (paymentsMenuBtn) {
		// 	for (let btn of paymentsMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editPaymentBtn) {
		// 	editPaymentBtn.disabled = true;
		// 	editPaymentBtn.title = "You don't have permission to edit payments.";
		// 	editPaymentBtn.classList.add('button-ghost');
		// }

		if (deletePaymentBtn) {
			deletePaymentBtn.disabled = true;
			deletePaymentBtn.title = "You don't have permission to delete payments.";
			deletePaymentBtn.classList.add('button-ghost');
		}
    } else if (grantedPermission === 'manage_users') {
        console.log("El usuario puede gestionar usuarios");
        // Habilita solo lo permitido a este nivel
		// if (salesSite) {
		// 	salesSite.style.display = "none";
		// }
		// if (paymentsSite) {
		// 	paymentsSite.style.display = "none";
		// }
		// if (adminSite) {
		// 	adminSite.style.display = "none";
		// }

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

		// if (addMembersButton) {
		// 	addMembersButton.disabled = true;
		// 	addMembersButton.title = "You don't have permission to add members.";
		// 	addMembersButton.classList.add('button-ghost');
		// }

		// if (cardMenuBtn) {
		// 	for (let btn of cardMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (saleMenuBtn) {
		// 	for (let btn of saleMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (addSaleBtn) {
		// 	addSaleBtn.disabled = true;
		// 	addSaleBtn.title = "You don't have permission to add sales.";
		// 	addSaleBtn.classList.add('button-ghost');
		// }

		// if (editSaleBtn) {
		// 	editSaleBtn.disabled = true;
		// 	editSaleBtn.title = "You don't have permission to edit sales.";
		// 	editSaleBtn.classList.add('button-ghost');
		// }

		if (deleteSaleBtn) {
			deleteSaleBtn.disabled = true;
			deleteSaleBtn.title = "You don't have permission to delete sales.";
			deleteSaleBtn.classList.add('button-ghost');
		}

		// if (productMenuBtn) {
		// 	for (let btn of productMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editProductBtn) {
		// 	editProductBtn.disabled = true;
		// 	editProductBtn.title = "You don't have permission to edit products.";
		// 	editProductBtn.classList.add('button-ghost');
		// }

		if (deleteProductBtn) {
			deleteProductBtn.disabled = true;
			deleteProductBtn.title = "You don't have permission to delete products.";
			deleteProductBtn.classList.add('button-ghost');
		}

		// if (addProductBtn) {
		// 	addProductBtn.disabled = true;
		// 	addProductBtn.title = "You don't have permission to add products.";
		// 	addProductBtn.classList.add('button-ghost');
		// }

		// if (addCategoryBtn) {
		// 	addCategoryBtn.disabled = true;
		// 	addCategoryBtn.title = "You don't have permission to add categories.";
		// 	addCategoryBtn.classList.add('button-ghost');
		// }

		// if (addCustomersButton) {
		// 	addCustomersButton.disabled = true;
		// 	addCustomersButton.title = "You don't have permission to add customers.";
		// 	addCustomersButton.classList.add('button-ghost');
		// }

		// if (customersMenuBtn) {
		// 	for (let btn of customersMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editCustomerBtn) {
		// 	editCustomerBtn.disabled = true;
		// 	editCustomerBtn.title = "You don't have permission to edit customers.";
		// 	editCustomerBtn.classList.add('button-ghost');
		// }

		if (deleteCustomerBtn) {
			deleteCustomerBtn.disabled = true;
			deleteCustomerBtn.title = "You don't have permission to delete customers.";
			deleteCustomerBtn.classList.add('button-ghost');
		}

		// if (addPaymentsBtn) {
		// 	addPaymentsBtn.disabled = true;
		// 	addPaymentsBtn.title = "You don't have permission to add payments.";
		// 	addPaymentsBtn.classList.add('button-ghost');
		// }

		// if (paymentsMenuBtn) {
		// 	for (let btn of paymentsMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editPaymentBtn) {
		// 	editPaymentBtn.disabled = true;
		// 	editPaymentBtn.title = "You don't have permission to edit payments.";
		// 	editPaymentBtn.classList.add('button-ghost');
		// }

		if (deletePaymentBtn) {
			deletePaymentBtn.disabled = true;
			deletePaymentBtn.title = "You don't have permission to delete payments.";
			deletePaymentBtn.classList.add('button-ghost');
		}
    } else if (grantedPermission === 'edit_data') {
        console.log("El usuario puede editar datos");
        // Habilita solo lo permitido a este nivel
		// if (salesSite) {
		// 	salesSite.style.display = "none";
		// }
		if (paymentsSite) {
			paymentsSite.style.display = "none";
		}
		if (adminSite) {
			adminSite.style.display = "none";
		}

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

		if (addMembersButton) {
			addMembersButton.disabled = true;
			addMembersButton.title = "You don't have permission to add members.";
			addMembersButton.classList.add('button-ghost');
		}

		if (cardMenuBtn) {
			for (let btn of cardMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		// if (saleMenuBtn) {
		// 	for (let btn of saleMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (addSaleBtn) {
		// 	addSaleBtn.disabled = true;
		// 	addSaleBtn.title = "You don't have permission to add sales.";
		// 	addSaleBtn.classList.add('button-ghost');
		// }

		// if (editSaleBtn) {
		// 	editSaleBtn.disabled = true;
		// 	editSaleBtn.title = "You don't have permission to edit sales.";
		// 	editSaleBtn.classList.add('button-ghost');
		// }

		if (deleteSaleBtn) {
			deleteSaleBtn.disabled = true;
			deleteSaleBtn.title = "You don't have permission to delete sales.";
			deleteSaleBtn.classList.add('button-ghost');
		}

		// if (productMenuBtn) {
		// 	for (let btn of productMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editProductBtn) {
		// 	editProductBtn.disabled = true;
		// 	editProductBtn.title = "You don't have permission to edit products.";
		// 	editProductBtn.classList.add('button-ghost');
		// }

		if (deleteProductBtn) {
			deleteProductBtn.disabled = true;
			deleteProductBtn.title = "You don't have permission to delete products.";
			deleteProductBtn.classList.add('button-ghost');
		}

		// if (addProductBtn) {
		// 	addProductBtn.disabled = true;
		// 	addProductBtn.title = "You don't have permission to add products.";
		// 	addProductBtn.classList.add('button-ghost');
		// }

		if (addCategoryBtn) {
			addCategoryBtn.disabled = true;
			addCategoryBtn.title = "You don't have permission to add categories.";
			addCategoryBtn.classList.add('button-ghost');
		}

		// if (addCustomersButton) {
		// 	addCustomersButton.disabled = true;
		// 	addCustomersButton.title = "You don't have permission to add customers.";
		// 	addCustomersButton.classList.add('button-ghost');
		// }

		// if (customersMenuBtn) {
		// 	for (let btn of customersMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editCustomerBtn) {
		// 	editCustomerBtn.disabled = true;
		// 	editCustomerBtn.title = "You don't have permission to edit customers.";
		// 	editCustomerBtn.classList.add('button-ghost');
		// }

		if (deleteCustomerBtn) {
			deleteCustomerBtn.disabled = true;
			deleteCustomerBtn.title = "You don't have permission to delete customers.";
			deleteCustomerBtn.classList.add('button-ghost');
		}

		if (addPaymentsBtn) {
			addPaymentsBtn.disabled = true;
			addPaymentsBtn.title = "You don't have permission to add payments.";
			addPaymentsBtn.classList.add('button-ghost');
		}

		if (paymentsMenuBtn) {
			for (let btn of paymentsMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editPaymentBtn) {
			editPaymentBtn.disabled = true;
			editPaymentBtn.title = "You don't have permission to edit payments.";
			editPaymentBtn.classList.add('button-ghost');
		}

		if (deletePaymentBtn) {
			deletePaymentBtn.disabled = true;
			deletePaymentBtn.title = "You don't have permission to delete payments.";
			deletePaymentBtn.classList.add('button-ghost');
		}
    } else if (grantedPermission === 'create_data') {
        console.log("El usuario puede crear datos");
        // Habilita solo lo permitido a este nivel
		// if (salesSite) {
		// 	salesSite.style.display = "none";
		// }
		if (paymentsSite) {
			paymentsSite.style.display = "none";
		}
		if (adminSite) {
			adminSite.style.display = "none";
		}

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

		if (addMembersButton) {
			addMembersButton.disabled = true;
			addMembersButton.title = "You don't have permission to add members.";
			addMembersButton.classList.add('button-ghost');
		}

		if (cardMenuBtn) {
			for (let btn of cardMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		// if (saleMenuBtn) {
		// 	for (let btn of saleMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (addSaleBtn) {
		// 	addSaleBtn.disabled = true;
		// 	addSaleBtn.title = "You don't have permission to add sales.";
		// 	addSaleBtn.classList.add('button-ghost');
		// }

		// if (editSaleBtn) {
		// 	editSaleBtn.disabled = true;
		// 	editSaleBtn.title = "You don't have permission to edit sales.";
		// 	editSaleBtn.classList.add('button-ghost');
		// }

		if (deleteSaleBtn) {
			deleteSaleBtn.disabled = true;
			deleteSaleBtn.title = "You don't have permission to delete sales.";
			deleteSaleBtn.classList.add('button-ghost');
		}

		if (productMenuBtn) {
			for (let btn of productMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editProductBtn) {
			editProductBtn.disabled = true;
			editProductBtn.title = "You don't have permission to edit products.";
			editProductBtn.classList.add('button-ghost');
		}

		if (deleteProductBtn) {
			deleteProductBtn.disabled = true;
			deleteProductBtn.title = "You don't have permission to delete products.";
			deleteProductBtn.classList.add('button-ghost');
		}

		if (addProductBtn) {
			addProductBtn.disabled = true;
			addProductBtn.title = "You don't have permission to add products.";
			addProductBtn.classList.add('button-ghost');
		}

		if (addCategoryBtn) {
			addCategoryBtn.disabled = true;
			addCategoryBtn.title = "You don't have permission to add categories.";
			addCategoryBtn.classList.add('button-ghost');
		}

		// if (addCustomersButton) {
		// 	addCustomersButton.disabled = true;
		// 	addCustomersButton.title = "You don't have permission to add customers.";
		// 	addCustomersButton.classList.add('button-ghost');
		// }

		// if (customersMenuBtn) {
		// 	for (let btn of customersMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editCustomerBtn) {
		// 	editCustomerBtn.disabled = true;
		// 	editCustomerBtn.title = "You don't have permission to edit customers.";
		// 	editCustomerBtn.classList.add('button-ghost');
		// }

		if (deleteCustomerBtn) {
			deleteCustomerBtn.disabled = true;
			deleteCustomerBtn.title = "You don't have permission to delete customers.";
			deleteCustomerBtn.classList.add('button-ghost');
		}

		if (addPaymentsBtn) {
			addPaymentsBtn.disabled = true;
			addPaymentsBtn.title = "You don't have permission to add payments.";
			addPaymentsBtn.classList.add('button-ghost');
		}

		if (paymentsMenuBtn) {
			for (let btn of paymentsMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editPaymentBtn) {
			editPaymentBtn.disabled = true;
			editPaymentBtn.title = "You don't have permission to edit payments.";
			editPaymentBtn.classList.add('button-ghost');
		}

		if (deletePaymentBtn) {
			deletePaymentBtn.disabled = true;
			deletePaymentBtn.title = "You don't have permission to delete payments.";
			deletePaymentBtn.classList.add('button-ghost');
		}
    } else if (grantedPermission === 'manage_sales') {
		console.log("El usuario puede gestionar ventas");
        // Habilita solo lo permitido a este nivel
		// if (salesSite) {
		// 	salesSite.style.display = "none";
		// }
		if (paymentsSite) {
			paymentsSite.style.display = "none";
		}
		if (adminSite) {
			adminSite.style.display = "none";
		}

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

		if (addMembersButton) {
			addMembersButton.disabled = true;
			addMembersButton.title = "You don't have permission to add members.";
			addMembersButton.classList.add('button-ghost');
		}

		if (cardMenuBtn) {
			for (let btn of cardMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		// if (saleMenuBtn) {
		// 	for (let btn of saleMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (addSaleBtn) {
		// 	addSaleBtn.disabled = true;
		// 	addSaleBtn.title = "You don't have permission to add sales.";
		// 	addSaleBtn.classList.add('button-ghost');
		// }

		// if (editSaleBtn) {
		// 	editSaleBtn.disabled = true;
		// 	editSaleBtn.title = "You don't have permission to edit sales.";
		// 	editSaleBtn.classList.add('button-ghost');
		// }

		if (deleteSaleBtn) {
			deleteSaleBtn.disabled = true;
			deleteSaleBtn.title = "You don't have permission to delete sales.";
			deleteSaleBtn.classList.add('button-ghost');
		}

		if (productMenuBtn) {
			for (let btn of productMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editProductBtn) {
			editProductBtn.disabled = true;
			editProductBtn.title = "You don't have permission to edit products.";
			editProductBtn.classList.add('button-ghost');
		}

		if (deleteProductBtn) {
			deleteProductBtn.disabled = true;
			deleteProductBtn.title = "You don't have permission to delete products.";
			deleteProductBtn.classList.add('button-ghost');
		}

		if (addProductBtn) {
			addProductBtn.disabled = true;
			addProductBtn.title = "You don't have permission to add products.";
			addProductBtn.classList.add('button-ghost');
		}

		if (addCategoryBtn) {
			addCategoryBtn.disabled = true;
			addCategoryBtn.title = "You don't have permission to add categories.";
			addCategoryBtn.classList.add('button-ghost');
		}

		// if (addCustomersButton) {
		// 	addCustomersButton.disabled = true;
		// 	addCustomersButton.title = "You don't have permission to add customers.";
		// 	addCustomersButton.classList.add('button-ghost');
		// }

		// if (customersMenuBtn) {
		// 	for (let btn of customersMenuBtn) {
		// 		btn.disabled = true;
		// 		btn.title = "You don't have permission to access this feature.";
		// 		btn.classList.add('button-ghost');
		// 	}
		// }

		// if (editCustomerBtn) {
		// 	editCustomerBtn.disabled = true;
		// 	editCustomerBtn.title = "You don't have permission to edit customers.";
		// 	editCustomerBtn.classList.add('button-ghost');
		// }

		if (deleteCustomerBtn) {
			deleteCustomerBtn.disabled = true;
			deleteCustomerBtn.title = "You don't have permission to delete customers.";
			deleteCustomerBtn.classList.add('button-ghost');
		}

		if (addPaymentsBtn) {
			addPaymentsBtn.disabled = true;
			addPaymentsBtn.title = "You don't have permission to add payments.";
			addPaymentsBtn.classList.add('button-ghost');
		}

		if (paymentsMenuBtn) {
			for (let btn of paymentsMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editPaymentBtn) {
			editPaymentBtn.disabled = true;
			editPaymentBtn.title = "You don't have permission to edit payments.";
			editPaymentBtn.classList.add('button-ghost');
		}

		if (deletePaymentBtn) {
			deletePaymentBtn.disabled = true;
			deletePaymentBtn.title = "You don't have permission to delete payments.";
			deletePaymentBtn.classList.add('button-ghost');
		}
    } else if (grantedPermission === 'view_dashboard') {
        console.log("El usuario puede ver el dashboard");
        // Habilita solo lo permitido a este nivel
		if (salesSite) {
			salesSite.style.display = "none";
		}
		if (paymentsSite) {
			paymentsSite.style.display = "none";
		}
		if (adminSite) {
			adminSite.style.display = "none";
		}

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

		if (addMembersButton) {
			addMembersButton.disabled = true;
			addMembersButton.title = "You don't have permission to add members.";
			addMembersButton.classList.add('button-ghost');
		}

		if (cardMenuBtn) {
			for (let btn of cardMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (saleMenuBtn) {
			for (let btn of saleMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (addSaleBtn) {
			addSaleBtn.disabled = true;
			addSaleBtn.title = "You don't have permission to add sales.";
			addSaleBtn.classList.add('button-ghost');
		}

		if (editSaleBtn) {
			editSaleBtn.disabled = true;
			editSaleBtn.title = "You don't have permission to edit sales.";
			editSaleBtn.classList.add('button-ghost');
		}

		if (deleteSaleBtn) {
			deleteSaleBtn.disabled = true;
			deleteSaleBtn.title = "You don't have permission to delete sales.";
			deleteSaleBtn.classList.add('button-ghost');
		}

		if (productMenuBtn) {
			for (let btn of productMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editProductBtn) {
			editProductBtn.disabled = true;
			editProductBtn.title = "You don't have permission to edit products.";
			editProductBtn.classList.add('button-ghost');
		}

		if (deleteProductBtn) {
			deleteProductBtn.disabled = true;
			deleteProductBtn.title = "You don't have permission to delete products.";
			deleteProductBtn.classList.add('button-ghost');
		}

		if (addProductBtn) {
			addProductBtn.disabled = true;
			addProductBtn.title = "You don't have permission to add products.";
			addProductBtn.classList.add('button-ghost');
		}

		if (addCategoryBtn) {
			addCategoryBtn.disabled = true;
			addCategoryBtn.title = "You don't have permission to add categories.";
			addCategoryBtn.classList.add('button-ghost');
		}

		if (addCustomersButton) {
			addCustomersButton.disabled = true;
			addCustomersButton.title = "You don't have permission to add customers.";
			addCustomersButton.classList.add('button-ghost');
		}

		if (customersMenuBtn) {
			for (let btn of customersMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editCustomerBtn) {
			editCustomerBtn.disabled = true;
			editCustomerBtn.title = "You don't have permission to edit customers.";
			editCustomerBtn.classList.add('button-ghost');
		}

		if (deleteCustomerBtn) {
			deleteCustomerBtn.disabled = true;
			deleteCustomerBtn.title = "You don't have permission to delete customers.";
			deleteCustomerBtn.classList.add('button-ghost');
		}

		if (addPaymentsBtn) {
			addPaymentsBtn.disabled = true;
			addPaymentsBtn.title = "You don't have permission to add payments.";
			addPaymentsBtn.classList.add('button-ghost');
		}

		if (paymentsMenuBtn) {
			for (let btn of paymentsMenuBtn) {
				btn.disabled = true;
				btn.title = "You don't have permission to access this feature.";
				btn.classList.add('button-ghost');
			}
		}

		if (editPaymentBtn) {
			editPaymentBtn.disabled = true;
			editPaymentBtn.title = "You don't have permission to edit payments.";
			editPaymentBtn.classList.add('button-ghost');
		}

		if (deletePaymentBtn) {
			deletePaymentBtn.disabled = true;
			deletePaymentBtn.title = "You don't have permission to delete payments.";
			deletePaymentBtn.classList.add('button-ghost');
		}
	}
});
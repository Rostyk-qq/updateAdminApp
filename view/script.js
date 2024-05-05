document.addEventListener('DOMContentLoaded', (e) => {

    const addUpdateModal = new bootstrap.Modal(document.getElementById('add_update_modal'));
    const deleteModal = new bootstrap.Modal(document.getElementById('delete_modal'));
    const exitModal = new bootstrap.Modal(document.getElementById('exit_modal'));

    const bodyTable = document.getElementById('body_table');


    const addUpdateModalParams = {
        modalTitle: document.getElementById('title_modal'),
        modalForm: document.getElementById('add_update_form'),
        submitForm: document.getElementById('submit_button'),
        form: document.getElementById('add_update_form'),
        closeBtnTriggerFooter: document.getElementById('close-modal-footer'),
        closeBtnTriggerHeader: document.getElementById('close-modal-header'),
        action: null
    };
    const deleteParams = {
        form: document.getElementById('delete_form'),
        closeBtnTriggerFooter: document.getElementById('close-delete-modal-footer'),
        entry_num: document.getElementById('delete_entrance_number'),
        flat_num: document.getElementById('delete_flat_number'),
        owner: document.getElementById('delete_owner')
    }
    const exitParams = {
        form: document.getElementById('exit_form'),
        closeBtnTriggerFooter: document.getElementById('close-exit-modal-footer'),
        exitBtn: document.getElementById('exit')
    }
    const searchParams = {
        searchInput: document.getElementById('search_input'),
        search_btn: document.getElementById('search_btn'),
        action: 'search'
    }

    const alert = {
        permission_message: document.getElementById('permission_message'),
        permission_alert: document.getElementById('alert_permission'),
        permission_btn: document.getElementById('operate_permission')
    }

    const paramsDefault = {
        addButton: document.getElementById('add_btn'),
        updateButtons: document.querySelectorAll('.update-btn'),
        deleteButtons: document.querySelectorAll('.delete-btn'),
    };


    // open permission page
    alert.permission_btn.onclick = (e) => {
        e.preventDefault();
        window.location.href = 'http://localhost:63342/laba4/view/permission/permission.php';
    }


    // action on errors for update add
    function modalUpdateAddErrors() {
        document.getElementById('err_entrance_number').textContent = '';
        document.getElementById('err_flat_number').textContent = '';
        document.getElementById('err_owner_firstname').textContent = '';
        document.getElementById('err_owner_secondname').textContent = '';
        document.getElementById('err_phone_number_owner').textContent = '';
        document.getElementById('err_services_price').textContent = '';
        document.getElementById('err_tenants').textContent = '';
    }

    function errorLog(code) {
        switch (code) {
            case 1:
            case 2:
                return 'err_entrance_number';
            case 3:
            case 4:
            case 5:
                return 'err_flat_number';
            case 6:
                return 'err_owner_firstname';
            case 7:
                return 'err_owner_secondname';
            case 8:
            case 9:
            case 10:
            case 11:
                return 'err_phone_number_owner';
            case 12:
            case 13:
                return 'err_services_price';
            case 14:
            case 15:
                return 'err_tenants';
        }
    }

    // clear after close
    addUpdateModal._element.onclick = (e) => {
        if(!e.target.closest('#add_update_form')){
            modalUpdateAddErrors();
            addUpdateModalParams.form.reset();
        }
    }
    addUpdateModalParams.closeBtnTriggerHeader.onclick = (e) => {
        modalUpdateAddErrors();
        addUpdateModalParams.form.reset();
    }
    addUpdateModalParams.closeBtnTriggerFooter.onclick = (e) => {
        modalUpdateAddErrors();
        addUpdateModalParams.form.reset();
    }


    // update add delete actions exit
    paramsDefault.addButton.onclick = async (e) => {
        e.preventDefault();

        const permission = await checkPermission({permission: 'check', action: 'add_permission'});

        if(permission){
            document.getElementById('update_id').value = '0';

            addUpdateModalParams.opened = true;

            addUpdateModalParams.modalTitle.textContent = 'Add';
            addUpdateModalParams.submitForm.textContent = 'Add';

            addUpdateModalParams.action = 'add';

            addUpdateModal.show();
        }
        else{
            alert.permission_message.textContent = "You don't have permission to add!";
            alert.permission_alert.style.setProperty('transform', 'translateY(0%)');
            setTimeout(() => {
                alert.permission_alert.style.setProperty('transform', 'translateY(150%)');
            }, 3000);
        }
    }
    paramsDefault.updateButtons.forEach(btn => {
        buttonUpdate(btn);
    });
    paramsDefault.deleteButtons.forEach(btn => {
        buttonDelete(btn);
    });
    exitParams.exitBtn.onclick = (e) => {
        e.preventDefault();
        exitModal.show();
    }


    //add update delete submit
    addUpdateModalParams.form.onsubmit = async (e) => {
        e.preventDefault();

        const formData = new FormData(addUpdateModalParams.form);
        formData.append('action', addUpdateModalParams.action);

        const response = await data(formData).then(response => response.json());

        modalUpdateAddErrors();

        if (response.errors?.length >= 1 && response.status === false) {
            for (const error of response.errors) {
                const className = errorLog(parseInt(error.code));
                document.getElementById(className).textContent = error.message;
            }
        }
        else{
            if (addUpdateModalParams.action === 'add') {
                addEntry(response.newEntry);
                bindDataToUpdate(response.newEntry.id);
                bindForDelete(response.newEntry.id);

                const lenRows = document.querySelectorAll('#body_table tr');
                checkLength(lenRows);
            } else {
                updateEntry(response.newEntry);
            }
            addUpdateModalParams.closeBtnTriggerFooter.click();
            addUpdateModalParams.opened = false;
            modalUpdateAddErrors();
            addUpdateModalParams.form.reset();
        }
    }
    deleteParams.form.onsubmit = async (e) => {
        e.preventDefault();

        const formDataDelete = new FormData(deleteParams.form);
        formDataDelete.append('action', 'delete');

        const response = await data(formDataDelete).then(response => response.json());
        if (response.status && response.entry_id) {
            document.querySelector(`#body_table tr[data-entry="${parseInt(response.entry_id)}"]`).remove();

            const lenRows = document.querySelectorAll(`#body_table tr`);
            checkLength(lenRows);
        }
        else{
            alert.permission_message.textContent = response.message_permission;
            alert.permission_alert.style.setProperty('transform', 'translateY(0%)');
            setTimeout(() => {
                alert.permission_alert.style.setProperty('transform', 'translateY(150%)');
            }, 3000);
        }
        deleteParams.closeBtnTriggerFooter.click();
    }
    exitParams.form.onsubmit = (e) => {
        e.preventDefault();

        window.location.href = '../auth/enter/enter.php';
    }


    // front add, update
    function addEntry(newEntry) {
        const entry = `
        <tr data-entry="${newEntry.id}">
            <td class="entrance_number" >${newEntry.entrance_number}</td>
            <td class="flat_number" >${newEntry.flat_number}</td>
            <td class="owner_firstname" >${newEntry.owner_firstname}</td>
            <td class="owner_secondname" >${newEntry.owner_secondname}</td>
            <td class="phone_number_owner" >${newEntry.phone_number_owner}</td>
            <td class="services_price" >${newEntry.services_price}</td>
            <td class="tenants" >${newEntry.tenants}</td>
            <td>
                <div class="d-flex align-items-center flex-row">
                    <button data-delete-entry="${newEntry.id}" type="button" class="border border-end-0 rounded rounded-end-0 delete-btn">
                        <i class='fa fa-trash'></i>
                    </button>
                    <button data-update-entry="${newEntry.id}" type="button" class="border rounded rounded-start-0 update-btn">
                        <i class="fa fa-pen"></i>
                    </button>
                </div>
            </td>
        </tr>
        `;
        bodyTable.insertAdjacentHTML('beforeend', entry);
    }

    function updateEntry(newEntry) {
        const getEntryForUpdate = document.querySelector(`#body_table tr[data-entry="${parseInt(newEntry.id)}"]`);
        if (getEntryForUpdate) {
            getEntryForUpdate.querySelector('.entrance_number').textContent = newEntry.entrance_number;
            getEntryForUpdate.querySelector('.flat_number').textContent = newEntry.flat_number;
            getEntryForUpdate.querySelector('.owner_firstname').textContent = newEntry.owner_firstname;
            getEntryForUpdate.querySelector('.owner_secondname').textContent = newEntry.owner_secondname;
            getEntryForUpdate.querySelector('.phone_number_owner').textContent = newEntry.phone_number_owner;
            getEntryForUpdate.querySelector('.services_price').textContent = newEntry.services_price;
            getEntryForUpdate.querySelector('.tenants').textContent = newEntry.tenants;
        }
    }


    //Search
    searchParams.searchInput.oninput = (e) => {
        e.preventDefault();

        const bodyTable = document.getElementById('body_table');
        const rows = bodyTable.querySelectorAll('tr');

        let value = e.target.value;

        if (value.length > 0) {
            searchParams.search_btn.disabled = false;
        } else {
            searchParams.search_btn.disabled = true;
            rows.forEach(row => {
                row.classList.remove('none');
            });
            document.getElementById('empty_after_search')?.remove();
        }
    }

    searchParams.search_btn.onclick = async (e) => {
        e.preventDefault();

        const valueInput = searchParams.searchInput.value;

        const response = await data(Object.entries({
            value: valueInput,
            action: searchParams.action
        })).then(response => response.json());
        if (response.status === true) {
            search(response.searched);
        }
    }

    function search(searchedIds) {
        const bodyTable = document.getElementById('body_table');
        const rows = bodyTable.querySelectorAll('tr');

        rows.forEach(row => {
            const entryId = parseInt(row.getAttribute('data-entry'));

            if (searchedIds.some(obj => parseInt(obj.id) === entryId)) {
                row.classList.remove('none');
            } else {
                row.classList.add('none');
            }
        });
        checkLength(searchedIds);
    }


    // bind after add (update, delete)
    function bindDataToUpdate(id) {
        const btn = document.querySelector(`#body_table tr[data-entry="${parseInt(id)}"] td button[data-update-entry="${parseInt(id)}"]`);
        if (btn) {
            buttonUpdate(btn);
        }
    }

    function buttonUpdate(btn) {
        if (btn) {
            btn.onclick = async (e) => {
                e.preventDefault();

                const permission = await checkPermission({permission: 'check', action: 'update_permission'});

                if(permission){
                    addUpdateModalParams.modalTitle.textContent = 'Update';
                    addUpdateModalParams.submitForm.textContent = 'Update';

                    addUpdateModalParams.action = 'update';

                    const getId = parseInt(btn.getAttribute('data-update-entry'));
                    const getEntry = document.querySelector(`#body_table tr[data-entry="${getId}"]`);


                    addUpdateModalParams.form.elements['update_id'].value = getId;

                    addUpdateModalParams.form.elements['entrance_number'].value = getEntry.querySelector('.entrance_number').textContent;
                    addUpdateModalParams.form.elements['flat_number'].value = getEntry.querySelector('.flat_number').textContent;
                    addUpdateModalParams.form.elements['owner_firstname'].value = getEntry.querySelector('.owner_firstname').textContent;
                    addUpdateModalParams.form.elements['owner_secondname'].value = getEntry.querySelector('.owner_secondname').textContent;
                    addUpdateModalParams.form.elements['phone_number_owner'].value = getEntry.querySelector('.phone_number_owner').textContent;
                    addUpdateModalParams.form.elements['services_price'].value = getEntry.querySelector('.services_price').textContent;
                    addUpdateModalParams.form.elements['tenants'].value = getEntry.querySelector('.tenants').textContent;

                    addUpdateModal.show();
                }
                else{
                    alert.permission_message.textContent = "You don't have permission to update!";
                    alert.permission_alert.style.setProperty('transform', 'translateY(0%)');
                    setTimeout(() => {
                        alert.permission_alert.style.setProperty('transform', 'translateY(150%)');
                    }, 3000);
                }
            }
        }
    }

    // delete bind
    function bindForDelete(id) {
        const btn = document.querySelector(`#body_table tr[data-entry="${id}"] td button[data-delete-entry="${id}"]`);
        if (btn) {
            buttonDelete(btn);
        }
    }


    function buttonDelete(btn) {
        if (btn) {
            btn.onclick = async (e) => {
                e.preventDefault();

                const permission = await checkPermission({permission: 'check', action: 'delete_permission'});

                if(permission){
                    const getId = parseInt(btn.getAttribute('data-delete-entry'));
                    const getEntry = document.querySelector(`#body_table tr[data-entry="${getId}"]`);

                    deleteParams.form.elements['delete_id'].value = getId;

                    deleteParams.entry_num.textContent = getEntry.querySelector('.entrance_number').textContent;
                    deleteParams.flat_num.textContent = getEntry.querySelector('.flat_number').textContent;
                    deleteParams.owner.textContent = getEntry.querySelector('.owner_firstname').textContent + ' ' +
                        getEntry.querySelector('.owner_secondname').textContent;

                    deleteModal.show();
                }
                else{
                    alert.permission_message.textContent = "You don't have permission to delete!";
                    alert.permission_alert.style.setProperty('transform', 'translateY(0%)');
                    setTimeout(() => {
                        alert.permission_alert.style.setProperty('transform', 'translateY(150%)');
                    }, 3000);
                }
            }
        }
    }


    // checkLength
    function checkLength(rows){
        if(rows.length === 0){
            const emptyTr = `
                <tr id="empty_after_search" >
                    <td class="text-center fs-3" colspan="8" >Table is Empty!</td>
                </tr>  
                `;
            bodyTable.insertAdjacentHTML('beforeend', emptyTr);
        }
        else{
            document.getElementById('empty_after_search')?.remove();
        }
    }


    // check permission
    async function checkPermission(object){
        if(object){
            const response = await data(Object.entries(object)).then(response => response.json());
            if(response.status){
                return true;
            }
            return false;
        }
    }



    async function data(data) {
        const url = 'http://localhost:63342/laba4/view/validate.php';
        return await fetch(url, {
            method: 'POST',
            body: JSON.stringify(Object.fromEntries(data)),
            headers: {
                'Content-Type': 'application/json'
            }
        });
    }
});
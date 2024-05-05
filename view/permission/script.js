
document.addEventListener('DOMContentLoaded', (e) =>{

    const save_btn = document.getElementById('save_changes');
    const list_users = document.getElementById('tb_body_permission');
    const array = Array.from(list_users.querySelectorAll('tr'));

    const resArray = {};

    save_btn.onclick = async (e) => {
        let i = 0;
        for (const resArr of array) {
            let key = resArr.getAttribute('data-user');

            resArray[i] = {};
            resArray[i].user = key;
            resArray[i].select = resArr.querySelector('.select_switch input').checked ? 'Y' : 'N';
            resArray[i].insert = resArr.querySelector('.insert_switch input').checked ? 'Y' : 'N';
            resArray[i].update = resArr.querySelector('.update_switch input').checked ? 'Y' : 'N';
            resArray[i].delete = resArr.querySelector('.delete_switch input').checked ? 'Y' : 'N';
            i++;
        }

        const response = await data(resArray).then(response => response.json());
        if(response.status === true){
            updatePermissions(response.permissions);

            document.getElementById('alert_success').style.setProperty('transform', 'translateY(0%)');
            setTimeout(() => {
                document.getElementById('alert_success').style.setProperty('transform', 'translateY(150%)');
            }, 1500);
        }
    }


    function updatePermissions(permissions){
        if(permissions){
            for (const permission of permissions) {
                const getUser = document.querySelector(`#tb_body_permission  tr[data-user="${permission.User}"]`);
                if(getUser){
                    getUser.querySelector('.switch_select').checked = permission.Select_priv === 'Y';
                    getUser.querySelector('.switch_insert').checked = permission.Insert_priv === 'Y';
                    getUser.querySelector('.switch_update').checked = permission.Update_priv === 'Y';
                    getUser.querySelector('.switch_delete').checked = permission.Delete_priv === 'Y';
                }
            }
        }
    }

    async function data(data){
        const url = "http://localhost:63342/laba4/view/permission/validate.php";
        return await fetch(url, {
            method: 'POST',
            body: JSON.stringify(data),
            headers: {
                'Content-Type': 'application/json'
            }
        });
    }
});
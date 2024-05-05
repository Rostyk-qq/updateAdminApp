const modalLogin = new bootstrap.Modal(document.getElementById('login_modal'));

document.addEventListener('DOMContentLoaded', (e) => {
    e.preventDefault();

    modalLogin.show();

    const modal_login  = {
        form: document.getElementById('login_form')
    };

    function resetErrors(){
        document.getElementById('err_username_login').textContent = '';
        document.getElementById('err_password_login').textContent = '';
    }
    function logErrors(code){
        switch (code){
            case 1:
                return 'err_username_login';
            case 2:
                return 'err_password_login';
        }
    }

    modal_login.form.onsubmit = async (e) => {
        e.preventDefault();

        const formData = new FormData(modal_login.form);

        const response = await data(formData).then(response => response.json());

        resetErrors();

        if(response.errors?.length >= 1){
            for (const error of response.errors) {
                const className = logErrors(parseInt(error.code));
                document.getElementById(className).textContent = error.message;
            }
        }
        else{
            window.location.href = response.url;
        }
    }


    async function data(data){
        const url = 'http://localhost:63342/laba4/auth/enter/validate.php';
        return await fetch(url, {
            method: 'POST',
            body: JSON.stringify(Object.fromEntries(data)),
            headers: {
                'Content-Type': 'application/json'
            }
        });
    }
});

window.onclick = (e) => {
    modalLogin.show();
};
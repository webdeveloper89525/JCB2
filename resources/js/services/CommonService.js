export default class CommonService {
    is_authenticated() {
        return localStorage.getItem('api_token')
    }

    logout() {
        localStorage.clear();
    }

    save_login(data) {
        localStorage.setItem('api_token', data.api_token);
        localStorage.setItem('zoho_index', data.zoho_index);
        localStorage.setItem('name', data.name);
        localStorage.setItem('email', data.email);
        localStorage.setItem('zuid', data.zuid);
    }

    get_auth_name() {
        return localStorage.getItem('name');
    }

    get_auth_avatar() {
        return "https://contacts.zoho.com/file?ID=" + localStorage.getItem('zuid') + "&fs=thumb";
    }

    get_api_header() {
        return {
            headers: {
                'Authorization': `Bearer ${this.is_authenticated()}`
            }
        }
    }
}
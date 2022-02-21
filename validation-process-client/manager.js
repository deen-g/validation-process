;(function (window, undefined) {
    /**
     *
     * @type {string}
     */
    const url = 'http://127.0.0.1:8000/api/';
    let {axios} = window
    /**
     * http connection to backend with axios
     */
    axios = axios.create({
        baseURL: url
    });
    /**
     * Axios connection setup
     * @type {{post: ((function(): Promise<void>)|*),
     * get: ((function(): Promise<void>)|*),
     * constructor: req.constructor,
     * delete: ((function(): Promise<void>)|*)}}
     */
    let req = {
        constructor: function (params = {}, data = null, callback) {
            this.data = data;
            this.params = params;
            this.callback = callback;
        },
        post: async function () {
            try {
                const response = await axios.post(this.params.url, JSON.stringify({...this.data}))
                console.log(response)
                this.callback(response.data)
            } catch (e) {
                this.callback(e.response.data)
                console.log(e.response.data)
            }
        },
        get: async function () {
            console.log(this.params)
            const response = await axios.get(this.params.url)
            this.callback(response.data)
        },
        delete: async function () {
            const response = await axios.delete(this.params.url)
            this.callback(response.data)
        },
    }
    /**
     * array to hold all the local and server contacts
     * @type {*[]}
     */
    let list = []
    /**
     * Manager: contain method to add, delete, save, verification and load data on local and server
     * @type {{add: Manager.add, init: Manager.init, getObject: (function(*): {}), loadFromServer: Manager.loadFromServer, constructor: Manager.constructor, validator: (function(*): boolean), save: ((function(*=): Promise<void>)|*), addFormListener: Manager.addFormListener, render: (function(*): string), remove: Manager.remove, validate: Manager.validate}}
     */
    let Manager = {
        constructor: function (buttons, config) {
            this.http = Object.create(req);
            this._validateButton = buttons[0];
            this._addButton = buttons[1];
            this._removeButton = buttons[2];
            this._saveButton = buttons[3];
            this._els = config.fields || {};
            this._els1 = config.fields1 || {};
            this.output = config.output || '';
            this.msg = config.msg || '';
            this.init();
        },
        init: function () {
            this.addFormListener();
            //initial loading of server data if exist
            this.loadFromServer()
        },
        /**
         * Load data from server
         */
        loadFromServer: function () {
            this.http.constructor({url: url}, {}, (response) => {
                list = []
                let data = response[0]
                if (data) {
                    data = data.split(/\r?\n/)
                    for (let l in data) {
                        let string = data[l]
                        string = string.split(',')
                        if (string.length > 1) {
                            let obj = {
                                name: string[0],
                                email: string[1],
                                phone: string[0]
                            }
                            list.push(obj)
                        }
                    }
                    console.log(list)
                    document.querySelector(this.output).innerHTML = this.render(list)
                }
            })
            this.http.get()
        },
        /**
         * 4 event listenter for 4 buttons in the html page
         */
        addFormListener: function () {
            let validateButton = this._validateButton, _validateButton = document.querySelector(validateButton);
            _validateButton.addEventListener('click', this.validate.bind(this), false);
            let removeButton = this._removeButton, _removeButton = document.querySelector(removeButton);
            _removeButton.addEventListener('click', this.remove.bind(this), false);
            let addButton = this._addButton, _addButton = document.querySelector(addButton);
            _addButton.addEventListener('click', this.add.bind(this), false);
            let saveButton = this._saveButton, _saveButton = document.querySelector(saveButton);
            _saveButton.addEventListener('click', this.save.bind(this), false);
        },
        /**
         * render: method to display the array of contacts
         * @param list
         * @return {string}
         */
        render: function (list) {
            let len = list.length, html = "";
            for (let i = 0; i < len; i++) {
                let myObject = list[i];
                for (let x in myObject) {
                    html += ("| " + x + ": " + myObject[x] + " ");
                }
                html += "<br/>";
            }
            return html;
        },
        /**
         * Validate any inpute fields
         * @param elFields
         * @return {boolean}
         */
        validator: function (elFields) {
            let isValidated = true;
            for (let field in elFields) {
                let email = /\S+@\S+\.\S+/;
                let el = document.querySelector(field)
                    , elVal = el.value;
                let error = document.getElementById(el.id + '-error');
                error.innerHTML = ""
                error.classList.remove('show')
                if (elFields[field].require || elVal === '') {
                    isValidated = false
                    error.innerHTML = el.name + " is required"
                } else if (elFields[field].number && isNaN(elVal)) {
                    isValidated = false
                    error.innerHTML = "Invalid " + el.name + ' number'
                } else if (elFields[field].email && !email.test(elVal)) {
                    isValidated = false
                    error.innerHTML = "this " + el.name + ' is not a valid one'
                }
                error.classList.add('show')
                el.classList.add('invalid-input');
            }
            return isValidated
        },
        /**
         * method triggered by the validate button
         * @param e
         */
        validate: function (e) {
            let elFields = {
                ...this._els,
                ...this._els1
            };
            this.validator(elFields)
            e.preventDefault();
        },
        /**
         * Retrieve object from the form any form fields
         * @param elField
         * @return {{}}
         */
        getObject: function (elField) {
            let obj = {}
            for (let field in elField) {
                let el = document.querySelector(field);
                obj[el.name] = el.value
            }
            return obj
        },
        /**
         * method trigered by the remove button
         * @param e
         */
        remove: function (e) {
            let elField = this._els1;
            document.querySelector(this.output).innerHTML = ""
            let obj = this.getObject(elField)
            const str = Object.values(obj).join(',');
            this.http.constructor({url: "?q=" + str}, {}, (response) => {
                this.loadFromServer()
            })
            this.http.delete()
            e.preventDefault();
        },
        /**
         * method triggered by the add button
         * @param e
         */
        add: function (e) {
            document.querySelector(this.output).innerHTML = ""
            let elField = this._els;
            let obj = this.getObject(elField)
            list.push(obj)
            document.querySelector(this.output).innerHTML = this.render(list)
            console.log('add', obj)
            e.preventDefault();
        },
        /**
         * method triggered by the save button
         * @param e
         */
        save: async function (e) {
            let elField = this._els;
            // if(this.validator(elFields)){} removed for testing backend validation
            let obj = this.getObject(elField)
            this.http.constructor({url: url}, {...obj}, (response) => {
                console.log(response)
                if (!response.status) {
                    for (let er in response.error) {
                        let el = document.getElementById(er + '-error');
                        el.innerHTML = response.error[er]
                        el.classList.add('show')
                    }
                } else {
                    let el = document.querySelector(this.msg);
                    el.innerHTML = 'data created'
                    this.add(e)
                }
            })
            await this.http.post()
            e.preventDefault();
        },
    }
    let form = Object.create(Manager);
    form.constructor(['#validate-btn', '#add-btn', '#remove-btn', '#save-btn'], {
        fields: {
            '#name': {
                required: true,
                maxlength: 20
            },
            '#email': {
                maxlength: 10,
                email: true,
            },
            '#phone': {
                maxlength: 10,
                number: true,
            },

        },
        fields1: {
            '#name1': {
                required: true,
                maxlength: 20
            },
            '#email1': {
                maxlength: 10,
                email: true,
            },
            '#phone1': {
                maxlength: 10,
                number: true,
            },

        },
        output: '#contacts',
        msg: '#message',
    });
})(window, undefined);

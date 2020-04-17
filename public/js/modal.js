class Modal
{
    modalId;
    modal;

    constructor (id)
    {
        this.modalId = id;
        this.modal = $(id);
    }

    show (title, message)
    {
        this.modal.find (`${this.modalId}-title`).text (title);
        this.modal.find (`${this.modalId}-message`).text (message);

        this.modal.modal ('show');
    }
}

export default Modal;
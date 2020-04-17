class Toast 
{
    toastElement;

    constructor (selectQuery)
    {
        this.toastElement = $(selectQuery);

        this.show = this.show.bind(this);
    }

    show (title, message)
    {
        this.toastElement.find ('.toast-title').text (title);
        this.toastElement.find ('.toast-body').text (message);

        this.toastElement.toast({
            animation: true,
            autohide: true,
            delay: 1500
        });
        this.toastElement.toast('show');
    }
}

export default Toast;
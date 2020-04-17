class ListItem
{
    itemData = null;

    constructor ()
    {
        this.bindSource = this.bindSource.bind (this);
        this.create = this.create.bind (this);
    }

    bindSource (data)
    {
        this.itemData = data;

        return this;
    }

    create ()
    {
        throw new Error ('This method must be implemented!');
    }

    static layout (container, item, dataArray)
    {
        // Fade out all of the container's children
        for (let e of $(container).children ())  
            $(e).fadeOut (400, function () { $(this).remove (); });

        for (let dataRow of dataArray)
        {
            // Bind source
            item.bindSource (dataRow);
            // Append element to the container
            $(container).append (item.create ());
        }
    }
}

export default ListItem;
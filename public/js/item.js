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
        $(container).empty();

        for (let dataRow of dataArray)
        {
            // Bind source
            item.bindSource (dataRow);
            // Get a copy of element
            $(container).append (item.create ());
        }
    }
}

export default ListItem;
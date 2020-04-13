import ListItem from "../item.js";

class CategoryTableRow extends ListItem
{
    create ()
    {
        return `
        <tr>
            <td>${this.itemData.id}</td>
            <td>${this.itemData.name}</td>
            <td><img src="../media/edit-primary.png" class="icon btn-edit" data-toggle='modal' data-target='#modal-category' target-entity='category' record-key='${this.itemData.id}' cleanup='refresh_table_category'></td>
            <td><img src="../media/garbage.png" class="icon btn-delete" record-key='${this.itemData.id}' target-entity='category' cleanup='refresh_table_category'></td>
        </tr>
        `;
    }
}

export default CategoryTableRow;
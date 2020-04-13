import ListItem from "../item.js";

class ProductTableRow extends ListItem
{
    create ()
    {
        return `
        <tr>
            <td></td>
            <td>${this.itemData.name}</td>
            <td>${this.itemData.quantity}</td>
            <td>${this.itemData.price}</td>
            <td><img src="../media/edit-primary.png" class="icon btn-edit" data-toggle='modal' data-target='#modal-product' target-entity='product' record-key='${this.itemData.id}' cleanup='refresh_table_product' ></td>
            <td><img src="../media/garbage.png" class="icon btn-delete" record-key='${this.itemData.id}' target-entity='product' cleanup='refresh_table_product'></td>
        </tr>
        `;
    }
}

export default ProductTableRow;
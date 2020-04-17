import ListItem from "../item.js";

class FavoriteProductItem extends ListItem
{
    create ()
    {
        let photo = this.itemData.product.photo == undefined ? '' : `<img class='w-100 h-100 rounded-lg shadow' src='../${this.itemData.product.photo}' />`;

        return `
        <div class="showcase-item col-lg-12" record-key='${this.itemData.product.id}'>
            <div class="row selectable">
                <div class="col-lg-3 bg-main rounded-lg shadow p-0">${photo}</div>
                <div class="col-lg-9">
                    <h4 class="text-main-dark mt-2">${this.itemData.product.name}</h4>
                    <p>${this.itemData.product.description}</p>
                    <span class='position-absolute' style='bottom: 0px'>
                        <img src="../media/cart.png" class="icon clickable btn-add-to-cart" target-entity='cart' />
                    </span>
                    <span class="badge bg-main-dark text-white shadow">${this.itemData.product.price} $</span>
                    <span class='text-main-dark position-absolute d-none select-indicator' style='top: 0; right: 0'>
                        <img class='icon mt-3 mx-2' src='../media/selected.png' />
                    </span>
                </div>
            </div>
        </div>
        `;
    }
}

export default FavoriteProductItem;
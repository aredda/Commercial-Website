import ListItem from '../item.js';

class ProductItem extends ListItem
{
    create ()
    {
        return `
        <div class="showcase-item col-lg-6" record-key='${this.itemData.id}'>
            <div class="row">
                <div class="col-lg-5 bg-main rounded-lg shadow"></div>
                <div class="col-lg-7">
                    <h4 class="text-main-dark">${this.itemData.name}</h4>
                    <p>${this.itemData.description}</p>
                    <span class="badge bg-main-dark text-white shadow">${this.itemData.price} $</span>
                    <span class='position-absolute' style='bottom: 0px'>
                        <span><img class='btn-add-to-cart icon clickable d-none mr-1' src='../media/cart.png' target-entity='cart'/></span>
                        <span><img class='btn-star icon clickable d-none' src='../media/star.png' target-entity='favorite'/></span>
                    </span>
                </div>
            </div>
        </div>
        `;
    }
}

export default ProductItem;
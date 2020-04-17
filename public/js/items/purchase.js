import ListItem from "../item.js";

class PurchaseItem extends ListItem
{
    create ()
    {
        let productElements = ``;


        for (let detail of this.itemData.purchaseDetails)
        {
            let photo = detail.product.photo == undefined ? '' : `<img class='w-100 h-100 rounded-lg shadow' src='../${detail.product.photo}' />`;

            productElements += `
            <div class="showcase-item col-lg-12">
                <div class="row">
                    <div class="col-lg-3 bg-main rounded-lg shadow p-0">${photo}</div>
                    <div class="col-lg-9">
                        <h4 class="text-main-dark">${detail.product.name}</h4>
                        <p>${detail.product.description}</p>
                        <span class="badge bg-success text-white shadow">${detail.product.price * detail.quantity} $</span>
                        <span class="badge bg-main-dark text-white shadow mx-2">${detail.quantity} Units</span>
                        <span class="badge bg-main-dark text-white shadow">${detail.product.price} $</span>
                    </div>
                </div>
            </div>
            `;
        }

        return `
        <div class="col-lg-12 mb-3 border rounded-top shadow bg-main-light purchase-item">
            <div class="row bg-main-gradient text-white font-weight-bold p-2">
                <div class="col-8">Date: ${this.itemData.date.date.split (' ')[0]}</div>
                <div class="col-4 text-right">Total: ${this.itemData.totalPrice} $</div>
            </div>
            <div class="row p-4">${productElements}</div>
        </div>
        `;
    }
}

export default PurchaseItem;
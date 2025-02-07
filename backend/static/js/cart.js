
const cart_size = document.getElementById("cart_size");
const cart_total = document.getElementById("cart_total");
const checkout_button = document.getElementById("checkout_button");
const cart_list = document.getElementById("cart_list");
const cart_menu = document.getElementById("cart_menu");
const cart_checkout = document.getElementById("cart_checkout");
const cart_checkout_total = document.getElementById("cart_checkout_total");


const toggle_cart = () => {
    cart_menu.classList.toggle("hidden");
}

const read_cart = () => {
    let x = window.localStorage.getItem('cart');
    if (!x) {
        return [];
    }

    try {
        x = JSON.parse(x)
        return x;
    }
    catch (err) {
        window.localStorage.removeItem('cart');
        return [];
    }
}

const get_cart = () => {
    return cart.content;
}

const set_cart = (_cart) => {
    cart.set(_cart);
}

const delete_cart = (id) => {
    let x = get_cart();

    cart.set(x.filter(xx => {
        return xx.id !== id;
    }));
}

const add_cart = () => {
    let x = get_cart();

    const book = {
        id: Number(document.getElementById("book_id").value),
        name: document.getElementById("book_name").value,
        price: document.getElementById("book_price").value,
        author: document.getElementById("book_author").value,
        picture: document.getElementById("book_picture").value,
    }

    let found = false;
    // check if book is already in cart

    x.forEach((item) => {
        if (item.id === book.id) {
            item.quantity += 1;
            found = true;
        }
    });

    if (!found) {
        x.push({
            ...book,
            quantity: 1,
        });
    }

    cart.set(x);
}

const empty_cart = () => {
    cart.set([]);
}

const cart = {
    set: (x) => {
        cart.content = x;
        const total = (cart.content.map(x => x.quantity * x.price).reduce((a, b) => a + b, 0) / 100).toFixed(2)

        const elements = cart.content.map(x => x.quantity).reduce((a, b) => a + b, 0);
        cart_size.innerText = elements;
        cart_total.innerText = "$" + total;

        if (elements == 0) {
            cart_size.classList.add("hidden")
            checkout_button.setAttribute("disabled", "");
        }
        else {
            cart_size.classList.remove("hidden")
            checkout_button.removeAttribute("disabled", "");
        }

        cart_list.innerHTML = "";
        cart.content.forEach((book) => {
            let item = make_list_item(book)
            cart_list.appendChild(item);
        });

        if (cart_checkout) {
            cart_checkout.innerHTML = "";
            cart.content.forEach((book, index) => {
                item = make_checkout_item(book, index);
                cart_checkout.appendChild(item);
            })

            cart_checkout_total.innerText = "Total: $" + total;
        }


        window.localStorage.setItem('cart', JSON.stringify(cart.content));
    },
}

const make_list_item = (book) => {
    const li = document.createElement("li");
    li.classList.add("flex", "py-6");

    const div1 = document.createElement("div");
    div1.classList.add("h-24", "w-24", "flex-shrink-0", "overflow-hidden", "rounded-md", "border", "border-gray-200");

    const img = document.createElement("img");
    img.classList.add("h-full", "w-full", "object-cover", "object-center");
    img.src = "/static/books/" + book.picture;
    img.alt = "Book cover for " + book.name;

    const div2 = document.createElement("div");
    div2.classList.add("ml-4", "flex-1", "flex", "flex-col");

    const div3 = document.createElement("div");
    div3.classList.add("flex", "justify-between", "text-base", "font-medium", "text-gray-900");

    const h3 = document.createElement("h3");
    h3.classList.add("text-base", "font-medium", "text-gray-900");

    const anchor = document.createElement("a");
    anchor.href = `/book.php?book_id=${book.id}`;
    anchor.innerText = book.name;
    h3.appendChild(anchor);

    const p1 = document.createElement("p");
    p1.classList.add("ml-4");
    p1.innerText = "$" + (book.price / 100).toFixed(2);

    const p2 = document.createElement("p");
    p2.classList.add("mt-1", "text-sm", "text-gray-500");
    p2.innerText = book.author;

    const div4 = document.createElement("div");
    div4.classList.add("flex", "flex-1", "items-end", "justify-between", "text-sm");

    const p3 = document.createElement("p");
    p3.classList.add("text-gray-500");
    p3.innerText = "Qty " + book.quantity;

    const div5 = document.createElement("div");
    div5.classList.add("flex");

    const button = document.createElement("button");
    button.classList.add("font-medium", "text-blue-600", "hover:text-blue-500");
    button.innerText = "Remove";
    button.onclick = () => delete_cart(book.id);

    div5.appendChild(button);
    div4.appendChild(p3);
    div4.appendChild(div5);
    div3.appendChild(h3);
    div3.appendChild(p1);
    div2.appendChild(div3);
    div2.appendChild(p2);
    div1.appendChild(img);
    li.appendChild(div1);
    li.appendChild(div2);
    li.appendChild(div4);

    return li;
}

const make_checkout_item = (book, index) => {
    const div = document.createElement("div");
    div.classList.add("flex")

    const input_id = document.createElement("input");
    input_id.type = "hidden";
    input_id.name = `cart[${index}][book_id]`;
    input_id.value = book.id;


    const input_quantity = document.createElement("input");
    input_quantity.type = "hidden";
    input_quantity.name = `cart[${index}][quantity]`;
    input_quantity.value = book.quantity;

    div.appendChild(input_id);
    div.appendChild(input_quantity);

    const div_render = document.createElement("div");
    div_render.classList.add("flex");
    div_render.innerText = "- " + book.name + "\t" + book.quantity + " x $" + (book.price / 100).toFixed(2);
    div.appendChild(div_render);

    return div;
}

cart.set(read_cart());
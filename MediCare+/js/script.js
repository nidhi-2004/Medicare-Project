// =========================================
// MEDICARE+ JAVASCRIPT
// =========================================


// Get the mobile menu button
const menuBtn = document.getElementById("menuBtn");


// Get the navigation menu
const navMenu = document.getElementById("navMenu");


// Check that both elements exist
if (menuBtn && navMenu) {

    // When the menu button is clicked
    menuBtn.addEventListener("click", function () {

        // Show or hide the navigation menu
        navMenu.classList.toggle("show");


        // Get the menu icon
        const icon = menuBtn.querySelector("i");


        // Change menu icon
        if (navMenu.classList.contains("show")) {

            icon.classList.remove("fa-bars");

            icon.classList.add("fa-xmark");

        } else {

            icon.classList.remove("fa-xmark");

            icon.classList.add("fa-bars");

        }

    });

}



// =========================================
// CLOSE MOBILE MENU AFTER CLICKING A LINK
// =========================================

const navigationLinks =
    document.querySelectorAll("#navMenu a");


navigationLinks.forEach(function (link) {

    link.addEventListener("click", function () {

        // Hide mobile menu
        navMenu.classList.remove("show");


        // Change icon back to menu
        const icon = menuBtn.querySelector("i");

        if (icon) {

            icon.classList.remove("fa-xmark");

            icon.classList.add("fa-bars");

        }

    });

});

/* =========================================
   MEDICARE+ PHARMACY
   ========================================= */

document.addEventListener("DOMContentLoaded", async function () {

    // Load the pharmacy inventory from MySQL so admin changes appear on the website.
    const medicineGrid = document.querySelector(".medicine-grid");
    if (medicineGrid) {
        try {
            const response = await fetch("get_medicines.php");
            if (response.ok) {
                const products = await response.json();
                if (Array.isArray(products)) {
                    medicineGrid.innerHTML = products.map(function (product) {
                        const name = String(product.name || "");
                        const safeName = name.replace(/[&<>"']/g, function (ch) {
                            return ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[ch]);
                        });
                        const price = Number(product.price || 0);
                        const stock = Number(product.stock || 0);
                        const normalizedName = name.toLowerCase();
                        const category = normalizedName.includes("vitamin") ? "vitamins"
                            : (normalizedName.includes("cold") || normalizedName.includes("fever") || normalizedName.includes("flu")) ? "cold"
                            : (normalizedName.includes("digest") || normalizedName.includes("antacid") || normalizedName.includes("stomach")) ? "digestive"
                            : "pain";
                        const categoryLabel = category.charAt(0).toUpperCase() + category.slice(1);
                        return `<div class="medicine-card" data-category="${category}" data-name="${safeName}">
                            <div class="medicine-image"><i class="fa-solid fa-pills"></i>
                            <span class="stock-badge">${stock > 0 ? "In Stock: " + stock : "Out of Stock"}</span></div>
                            <div class="medicine-content"><span class="medicine-category">${categoryLabel}</span>
                            <h3>${safeName}</h3><p>Available through MediCare+ pharmacy.</p>
                            <div class="medicine-bottom"><strong>₹${price}</strong>
                            <button class="add-cart" data-name="${safeName}" data-price="${price}" ${stock < 1 ? "disabled" : ""}>
                            <i class="fa-solid fa-cart-plus"></i> Add to Cart</button></div></div></div>`;
                    }).join("");
                }
            }
        } catch (error) {
            console.error("Could not load pharmacy inventory:", error);
        }
    }

    /* =====================================
       ELEMENTS
       ===================================== */

    const searchInput =
        document.getElementById("medicineSearch");

    const medicineCards =
        document.querySelectorAll(".medicine-card");

    const categoryButtons =
        document.querySelectorAll(".category-btn");

    const addCartButtons =
        document.querySelectorAll(".add-cart");

    const cartButton =
        document.getElementById("cartButton");

    const cartModal =
        document.getElementById("cartModal");

    const closeCart =
        document.getElementById("closeCart");

    const cartOverlay =
        document.getElementById("cartOverlay");

    const cartItemsContainer =
        document.getElementById("cartItems");

    const cartEmpty =
        document.getElementById("cartEmpty");

    const cartFooter =
        document.getElementById("cartFooter");

    const cartCount =
        document.getElementById("cartCount");

    const cartTotal =
        document.getElementById("cartTotal");

    const clearCart =
        document.getElementById("clearCart");

    const checkoutButton =
        document.getElementById("checkoutButton");


    /* =====================================
       CART DATA
       ===================================== */

    let cart = [];


    /* =====================================
       ADD TO CART
       ===================================== */

    addCartButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            const name =
                this.dataset.name;

            const price =
                Number(this.dataset.price);


            const existingItem =
                cart.find(function (item) {

                    return item.name === name;

                });


            if (existingItem) {

                existingItem.quantity++;

            } else {

                cart.push({

                    name: name,

                    price: price,

                    quantity: 1

                });

            }


            updateCart();


            this.innerHTML =
                '<i class="fa-solid fa-check"></i> Added';


            setTimeout(() => {

                this.innerHTML =
                    '<i class="fa-solid fa-cart-plus"></i> Add to Cart';

            }, 1000);

        });

    });


    /* =====================================
       UPDATE CART
       ===================================== */

    function updateCart() {

        cartItemsContainer.innerHTML = "";


        let totalItems = 0;

        let totalPrice = 0;


        if (cart.length === 0) {

            cartEmpty.style.display = "block";

            cartFooter.style.display = "none";

        } else {

            cartEmpty.style.display = "none";

            cartFooter.style.display = "block";


            cart.forEach(function (item, index) {


                totalItems += item.quantity;


                totalPrice +=
                    item.price * item.quantity;


                const cartItem =
                    document.createElement("div");


                cartItem.className =
                    "cart-item";


                cartItem.innerHTML = `

                    <div class="cart-item-info">

                        <h3>
                            ${item.name}
                        </h3>

                        <p>
                            ₹${item.price} per item
                        </p>

                        <div class="cart-item-controls">

                            <button
                                class="quantity-btn decrease"
                                data-index="${index}">
                                −
                            </button>

                            <span class="quantity-number">
                                ${item.quantity}
                            </span>

                            <button
                                class="quantity-btn increase"
                                data-index="${index}">
                                +
                            </button>

                            <button
                                class="remove-cart-item"
                                data-index="${index}">
                                Remove
                            </button>

                        </div>

                    </div>


                    <div class="cart-item-price">

                        ₹${item.price * item.quantity}

                    </div>

                `;


                cartItemsContainer.appendChild(cartItem);

            });

        }


        cartCount.textContent =
            totalItems;


        cartTotal.textContent =
            "₹" + totalPrice;


        addQuantityEvents();

    }


    /* =====================================
       QUANTITY BUTTONS
       ===================================== */

    function addQuantityEvents() {


        const decreaseButtons =
            document.querySelectorAll(".decrease");


        const increaseButtons =
            document.querySelectorAll(".increase");


        const removeButtons =
            document.querySelectorAll(".remove-cart-item");


        /* DECREASE */

        decreaseButtons.forEach(function (button) {

            button.addEventListener("click", function () {

                const index =
                    Number(this.dataset.index);


                if (cart[index].quantity > 1) {

                    cart[index].quantity--;

                } else {

                    cart.splice(index, 1);

                }


                updateCart();

            });

        });


        /* INCREASE */

        increaseButtons.forEach(function (button) {

            button.addEventListener("click", function () {

                const index =
                    Number(this.dataset.index);


                cart[index].quantity++;


                updateCart();

            });

        });


        /* REMOVE */

        removeButtons.forEach(function (button) {

            button.addEventListener("click", function () {

                const index =
                    Number(this.dataset.index);


                cart.splice(index, 1);


                updateCart();

            });

        });

    }


    /* =====================================
       OPEN CART
       ===================================== */

    if (cartButton) {

        cartButton.addEventListener("click", function () {

            cartModal.classList.add("show");

            document.body.style.overflow = "hidden";

        });

    }


    /* =====================================
       CLOSE CART
       ===================================== */

    function closeCartModal() {

        cartModal.classList.remove("show");

        document.body.style.overflow = "";

    }


    closeCart.addEventListener(
        "click",
        closeCartModal
    );


    cartOverlay.addEventListener(
        "click",
        closeCartModal
    );


    /* =====================================
       CLEAR CART
       ===================================== */

    clearCart.addEventListener("click", function () {

        if (cart.length === 0) {

            return;

        }


        const confirmClear =
            confirm(
                "Are you sure you want to clear your cart?"
            );


        if (confirmClear) {

            cart = [];

            updateCart();

        }

    });


    /* =====================================
       PROCEED TO ORDER
       ===================================== */

const checkoutBtn = checkoutButton;
if (checkoutBtn) {
    checkoutBtn.addEventListener("click", async function () {
        if (cart.length === 0) {
            alert("Please add medicines to your cart.");
            return;
        }

        const name = prompt("Enter your full name:");
        if (!name || !name.trim()) return;

        const mobile = prompt("Enter your 10-digit mobile number:");
        if (!mobile || !/^[0-9]{10}$/.test(mobile.trim())) {
            alert("Please enter a valid 10-digit mobile number.");
            return;
        }

        try {
            const response = await fetch("save_pharmacy_order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    name: name.trim(),
                    mobile: mobile.trim(),
                    items: cart
                })
            });

            const result = await response.json();

           
if (result.success) {
    alert(result.message);

    cart.length = 0;
    updateCart();
} else {
    alert(result.message || "Order could not be saved.");
}
 
        } catch (error) {
            console.error(error);
            alert("Server error. Check XAMPP and try again.");
        }
    });
}



    /* =====================================
       SEARCH
       ===================================== */

    if (searchInput) {

        searchInput.addEventListener(
            "input",
            function () {

                const searchText =
                    this.value
                    .toLowerCase()
                    .trim();


                medicineCards.forEach(
                    function (card) {

                        const medicineName =
                            card.dataset.name
                            .toLowerCase();


                        if (
                            medicineName.includes(
                                searchText
                            )
                        ) {

                            card.style.display =
                                "";

                        } else {

                            card.style.display =
                                "none";

                        }

                    }
                );

            }
        );

    }


    /* =====================================
       CATEGORY FILTER
       ===================================== */

    categoryButtons.forEach(
        function (button) {

            button.addEventListener(
                "click",
                function () {


                    categoryButtons.forEach(
                        function (btn) {

                            btn.classList.remove(
                                "active"
                            );

                        }
                    );


                    this.classList.add("active");


                    const category =
                        this.dataset.category;


                    medicineCards.forEach(
                        function (card) {


                            if (
                                category === "all" ||
                                card.dataset.category === category
                            ) {

                                card.style.display =
                                    "";

                            } else {

                                card.style.display =
                                    "none";

                            }

                        }
                    );

                }
            );

        }
    );


    /* =====================================
       INITIAL CART
       ===================================== */

    updateCart();


});

// =========================================
// DEPARTMENT - DOCTOR SELECTION
// =========================================



    
document.addEventListener("DOMContentLoaded", function () {
    const department = document.getElementById("department");
    const doctor = document.getElementById("doctor");

    if (!department || !doctor) return;

    department.addEventListener("change", async function () {
        const selectedDepartment = department.value;

        doctor.innerHTML = '<option value="">Loading doctors...</option>';
        doctor.disabled = true;

        if (!selectedDepartment) {
            doctor.innerHTML = '<option value="">Select Department First</option>';
            return;
        }

        try {
            const response = await fetch(
                "get_doctors.php?department=" +
                encodeURIComponent(selectedDepartment)
            );

            if (!response.ok) {
                throw new Error("Unable to load doctors");
            }

            const doctors = await response.json();

            doctor.innerHTML = '<option value="">Select Doctor</option>';

            if (doctors.length === 0) {
                doctor.innerHTML = '<option value="">No doctors available</option>';
                return;
            }

            doctors.forEach(function (item) {
                const option = document.createElement("option");
                option.value = item.id;
                option.textContent = item.name;
                doctor.appendChild(option);
            });

            doctor.disabled = false;
        } catch (error) {
            doctor.innerHTML = '<option value="">Could not load doctors</option>';
            console.error(error);
        }
    });
});

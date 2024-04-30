jQuery(document).ready(function ($) {
  var focus_qty;

  // Open cart pop up.
  function open_popup() {
    $(".wcacn-cp-opac").show();
    $(".wcacn-cp-modal").addClass("wcacn-cp-active");

    if (
      wcacn_cp_localize.closeafter != "" &&
      wcacn_cp_localize.closeafter != 0
    ) {
      setTimeout(function () {
        $(".wcacn-cp-close , .wcacn-cp-modal").trigger("click");
      }, wcacn_cp_localize.closeafter * 1000);
    }
  }

  // On add to cart.
  $(document.body).on("added_to_cart", function () {
    open_popup();
  });

  // Close Popup.
  function close_popup(e) {
    $.each(e.target.classList, function (key, value) {
      if (value == "wcacn-cp-close" || value == "wcacn-cp-modal") {
        $(".wcacn-cp-opac").hide();
        $(".wcacn-cp-modal").removeClass("wcacn-cp-active");
        $(".wcacn-cp-atcn , .wcacn-cp-content").html("");
      }
    });
  }

  $(document).on("click", ".wcacn-cp-close , .wcacn-cp-modal", close_popup);

  // Block popup/
  function block_popup() {
    $(".wcacn-cp-outer").show();
  }

  // Unblock popup.
  function unblock_popup() {
    $(".wcacn-cp-outer").hide();
  }

  // Reset cart button/form.
  function reset_cart(atc_btn) {
    $(".wcacn-cp-added", atc_btn).remove();
    var qty_elem = atc_btn.parents("form.cart").find(".qty");
    if (qty_elem.length > 0) qty_elem.val(qty_elem.attr("min") || 1);
    $(".added_to_cart").remove();
  }

  // Notice Function.
  function show_notice(notice_type, notice) {
    $(".wcacn-cp-notice")
      .html(notice)
      .attr("class", "wcacn-cp-notice")
      .addClass("wcacn-cp-nt-" + notice_type);
    $(".wcacn-cp-notice-box").fadeIn("fast");
    clearTimeout(fadenotice);
    var fadenotice = setTimeout(function () {
      $(".wcacn-cp-notice-box").fadeOut("slow");
    }, 3000);
  }

  // Add to cart function.
  function add_to_cart(atc_btn, form_data) {
    // Trigger event.
    $(document.body).trigger("adding_to_cart", [atc_btn, form_data]);

    $.ajax({
      url: wcacn_cp_localize.wc_ajax_url
        .toString()
        .replace("%%endpoint%%", "wcacn_cp_add_to_cart"),
      type: "POST",
      data: $.param(form_data),
      success: function (response) {
        $(".wcacn-cp-adding", atc_btn).remove();

        if (response.fragments) {
          // Trigger event so themes can refresh other areas.
          $(document.body).trigger("added_to_cart", [
            response.fragments,
            response.cart_hash,
            atc_btn,
          ]);
          atc_btn.append(
            '<span class="wcacn-cp-icon-check wcacn-cp-added"></span>'
          );
        } else if (response.error) {
          show_notice("error", response.error);
        } else {
          console.log(response);
        }

        // Reset to default.
        if (wcacn_cp_localize.reset_cart) reset_cart(atc_btn);
      },
    });
  }

  // Add to cart on single page.
  $(document).on("submit", "form.cart", function (e) {
    var form = $(this);
    var atc_btn = form.find('button[type="submit"]');

    var form_data = form.serializeArray();

    // If button as name add-to-cart get it and add to form.
    if (
      atc_btn.attr("name") &&
      atc_btn.attr("name") == "add-to-cart" &&
      atc_btn.attr("value")
    ) {
      form_data.push({ name: "add-to-cart", value: atc_btn.attr("value") });
    }

    var is_valid = false;

    $.each(form_data, function (index, data) {
      if (data.name === "add-to-cart") {
        is_valid = true;
        return false;
      }
    });

    if (is_valid) {
      e.preventDefault();
    } else {
      return;
    }

    $(".wcacn-cp-added", atc_btn).remove();
    atc_btn.append(
      '<span class="wcacn-cp-icon-spinner wcacn-cp-adding" aria-hidden="true"></span>'
    );

    form_data.push({ name: "action", value: "wcacn_cp_add_to_cart" });

    add_to_cart(atc_btn, form_data); // Ajax add to cart.
  });

  // Ajax function to update cart (In a popup).
  function wcacn_cp_update_ajax(cart_key, new_qty, pid) {
    return $.ajax({
      url: wcacn_cp_localize.adminurl,
      type: "POST",
      data: {
        action: "wcacn_cp_change_ajax",
        cart_key: cart_key,
        new_qty: new_qty,
        pid: pid,
      },
    });
  }

  // Update cart.
  function update_cart(cart_key, new_qty) {
    block_popup();
    $.ajax({
      url: wcacn_cp_localize.wc_ajax_url
        .toString()
        .replace("%%endpoint%%", "wcacn_cp_update_cart"),
      type: "POST",
      data: {
        cart_key: cart_key,
        new_qty: new_qty,
      },
      success: function (response) {
        if (response.fragments) {
          var fragments = response.fragments,
            cart_hash = response.cart_hash;

          // Set fragments.
          $.each(response.fragments, function (key, value) {
            $(key).replaceWith(value);
            $(key).stop(true).css("opacity", "1").unblock();
          });

          if (wc_cart_fragments_params) {
            var cart_hash_key =
              wc_cart_fragments_params.ajax_url.toString() + "-wc_cart_hash";
            // Set cart hash.
            sessionStorage.setItem(
              wc_cart_fragments_params.fragment_name,
              JSON.stringify(fragments)
            );
            localStorage.setItem(cart_hash_key, cart_hash);
            sessionStorage.setItem(cart_hash_key, cart_hash);
          }

          $(document.body).trigger("wc_fragments_loaded");
        } else {
          // Print error.
          // show_notice('error',response.error);
          console.log(response);
        }

        unblock_popup();
      },
    });
  }

  // Save Quantity on focus.
  $(document).on("focusin", ".wcacn-cp-qty", function () {
    focus_qty = $(this).val();
  });

  // Qty input on change.
  $(document).on("change", ".wcacn-cp-qty", function (e) {
    var _this = $(this);
    var new_qty = parseFloat($(this).val());
    var step = parseFloat($(this).attr("step"));
    var min_value = parseFloat($(this).attr("min"));
    var max_value = parseFloat($(this).attr("max"));
    var invalid = false;

    if (new_qty === 0) {
      _this
        .parents(".wcacn-cp-pdetails")
        .find(".wcacn-cp-remove-pd")
        .trigger("click");
      return;
    }
    // Check If valid number.
    else if (isNaN(new_qty) || new_qty < 0) {
      invalid = true;
    }

    // Check maximum quantity.
    else if (new_qty > max_value && max_value > 0) {
      alert("Maximum Quantity: " + max_value);
      invalid = true;
    }

    // Check Minimum Quantity.
    else if (new_qty < min_value) {
      invalid = true;
    }

    // Check Step.
    else if (new_qty % step !== 0) {
      alert("Quantity can only be purchased in multiple of " + step);
      invalid = true;
    }

    // Update if everything is fine.
    else {
      var cart_key = $(this).parents("tr").data("wcacn_cp_key");
      update_cart(cart_key, new_qty);
    }

    if (invalid === true) {
      $(this).val(focus_qty);
    }
  });

  // Plus minus buttons.
  $(document).on("click", ".wcacn-chng", function () {
    var _this = $(this);
    var qty_element = _this.siblings(".wcacn-cp-qty");
    qty_element.trigger("focusin");
    var input_qty = parseFloat(qty_element.val());

    var step = parseFloat(qty_element.attr("step"));
    var min_value = parseFloat(qty_element.attr("min"));
    var max_value = parseFloat(qty_element.attr("max"));

    if (_this.hasClass("wcacn-plus")) {
      var new_qty = input_qty + step;

      if (new_qty > max_value && max_value > 0) {
        alert("Maximum Quantity: " + max_value);
        return;
      }
    } else if (_this.hasClass("wcacn-minus")) {
      var new_qty = input_qty - step;
      if (new_qty === 0) {
        _this
          .parents(".wcacn-cp-pdetails")
          .find(".wcacn-cp-remove .wcacn-icon")
          .trigger("click");
        return;
      } else if (new_qty < min_value) {
        return;
      } else if (input_qty < 0) {
        alert("Invalid");
        return;
      }
    }

    var cart_key = $(this).parents("tr").data("wcacn_cp_key");

    update_cart(cart_key, new_qty);
  });

  // Remove item from cart.
  $(document).on("click", ".wcacn-cp-remove-pd", function (e) {
    e.preventDefault();
    var cart_key = $(this).parents("tr").data("wcacn_cp_key");
    update_cart(cart_key, 0);
  });
});

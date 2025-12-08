<div class="container">

    <!-- Top Bar -->
    <div class="top-bar">
      <h1 class="header-title">✉️ Message</h1><br />
    </div>

    <?php echo do_shortcode('[better_messages]'); ?>

</div>

<style>
.container {
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  padding: 20px;
  margin-bottom: 20px;
}

/* Top Bar Styling */
.bp-messages-wrap-main .chat-header {
    background-color: #3578c6 !important; /* Blue background */
    color: #FFF !important; /* White text */
    display: flex;
    align-items: center;
    padding: 10px 15px;
}

/* Make all icons inside the top bar white too */
.bp-messages-wrap-main .chat-header svg {
    color: #FFF !important;
    fill: #FFF !important;
}

/* Links and buttons in the header */
.bp-messages-wrap-main .chat-header a {
    color: #FFF !important;
}

/* Search input inside top bar (if needed) */
.bp-messages-wrap-main .chat-header input {
    background: #fff;
    color: #333;
    border: none;
    border-radius: 4px;
    padding: 5px 10px;
}
.bp-messages-wrap #bm-new-thread-title {
  color: #FFF !important;
}
</style>
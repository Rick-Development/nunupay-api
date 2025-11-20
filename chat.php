<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tidio Live Support</title>
    <script src="//code.tidio.co/b1ke3xl3hd5czkymanvfplq7jmqorxcf.js" async></script>
</head>
<body>

<script>
   (function() {
    function onTidioChatApiReady() {
      (function() {
          window.tidioChatApi.open();
      })();
    }
    if (window.tidioChatApi) {
      window.tidioChatApi.on("ready", onTidioChatApiReady);
    } else {
      document.addEventListener("tidioChat-ready", onTidioChatApiReady);
    }
    
  })();
   // Ensure that the "Exit Chat" button is hidden once the chat iframe is fully loaded
  document.addEventListener('DOMContentLoaded', function() {
    // This delay ensures the chat iframe is fully loaded before hiding the button
    setTimeout(function() {
      const exitChatButton = document.querySelector('.material-icons.exit-chat.ripple.tidio-a4q2e2');
      if (exitChatButton) {
        exitChatButton.style.display = 'none'; // Completely hides the element
      }
    }, 1000); // Adjust the timeout value as necessary
  });
</script>
</body>
</html>

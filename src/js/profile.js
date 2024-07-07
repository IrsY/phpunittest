document.addEventListener("DOMContentLoaded", function() {
    const profileForm = document.getElementById("profileForm");

    profileForm.addEventListener("submit", function(event) {
        event.preventDefault();

        const formData = new FormData(profileForm);

        fetch("process/process_profile_combined.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Profile updated successfully.");
                window.location.reload();
            } else {
                const errorMessages = document.querySelector(".error-messages");
                errorMessages.innerHTML = "";
                data.errors.forEach(error => {
                    const errorMessage = document.createElement("p");
                    errorMessage.textContent = error;
                    errorMessages.appendChild(errorMessage);
                });
            }
        })
        .catch(error => {
            console.error("Error:", error);
        });
    });
});
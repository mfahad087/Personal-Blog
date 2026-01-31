<?php
include 'includes/config.php';
$page_title = "Contact";
include 'includes/header.php';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Insert into database
    $query = "INSERT INTO messages (name, email, subject, message) 
              VALUES ('$name', '$email', '$subject', '$message')";
    
    if(mysqli_query($conn, $query)) {
        $success = "Message sent successfully!";
    } else {
        $error = "Error sending message. Please try again.";
    }
}
?>

<section class="contact-section">
    <div class="container">
        <h2 class="section-title">Contact Me</h2>
        
        <?php if(isset($success)): ?>
        <div class="alert success" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 30px;">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($error)): ?>
        <div class="alert error" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 30px;">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-card">
                <h3>Send Message</h3>
                <form id="contactForm" method="POST" onsubmit="sendEmail(event)">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
            
            <!-- Contact Buttons -->
            <div class="contact-card">
                <h3>Direct Contact</h3>
                <div class="contact-buttons">
                    <a href="https://wa.me/923289595180" target="_blank" class="contact-btn">
                        <i class="fab fa-whatsapp"></i>
                        <div>
                            <strong>WhatsApp</strong>
                            <span>Chat on WhatsApp</span>
                        </div>
                    </a>
                    <a href="mailto:saadfahad2gr@gmail.com" class="contact-btn">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <strong>Gmail</strong>
                            <span>Send Email</span>
                        </div>
                    </a>
                </div>
                
                <!-- Location moved here -->
                <div class="location-section" style="margin-top: 30px; padding-top: 30px; border-top: 1px solid var(--border-color);">
                    <h3 style="margin-bottom: 15px; color: var(--primary-color);">Location</h3>
                    <p style="margin-bottom: 20px; color: #666;">Based in Gujrat, Punjab, Pakistan</p>
                    <a href="https://maps.google.com/?q=Gujrat,Punjab,Pakistan" target="_blank" class="btn map-btn" style="display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-map-marker-alt"></i> View on Map
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: start;
}

.map-btn:hover {
    background-color: #c0392b !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
}

.location-section {
    animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}
</style>

<script>
function sendEmail(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Send email via EmailJS
    emailjs.send("service_xg23mn3", "template_letikl7", {
        from_name: formData.get('name'),
        from_email: formData.get('email'),
        subject: formData.get('subject'),
        message: formData.get('message')
    })
    .then(function(response) {
        alert('Message sent successfully!');
        form.submit(); // Submit to PHP handler
    }, function(error) {
        alert('Failed to send message. Please try again.');
        console.log('FAILED...', error);
        form.submit(); // Still submit to PHP handler
    });
}
</script>

<?php include 'includes/footer.php'; ?>
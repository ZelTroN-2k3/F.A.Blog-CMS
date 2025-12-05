<?php
include "core.php";
head();

// Note : head() ouvre déjà <div class="container"><div class="row">
// Nous devons donc commencer directement par les colonnes (col-md-...)

// Initialisation des variables
$error = "";
$success = "";

// --- TRAITEMENT DU FORMULAIRE (Identique à votre logique, juste compacté pour la lisibilité) ---
if (isset($_POST['send'])) {
    validate_csrf_token();
    $name = strip_tags(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST['subject']));
    $message = strip_tags(trim($_POST['message']));

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = '<div class="alert alert-danger fade show"><i class="fas fa-exclamation-circle me-2"></i> All fields are required.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '<div class="alert alert-danger fade show"><i class="fas fa-at me-2"></i> Invalid email address.</div>';
    } else {
        // Logique ReCAPTCHA
        $captcha_valid = true;
        if (!empty($settings['gcaptcha_secretkey'])) {
            $captcha_valid = false;
            if(!empty($_POST['g-recaptcha-response'])){
                $secretKey = $settings['gcaptcha_secretkey'];
                $responseKey = $_POST['g-recaptcha-response'];
                $userIP = $_SERVER['REMOTE_ADDR'];
                $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$responseKey&remoteip=$userIP";
                $response = file_get_contents($url);
                $responseKeys = json_decode($response, true);
                if ($responseKeys["success"]) $captcha_valid = true;
            }
        }

        if (!$captcha_valid) {
            $error = '<div class="alert alert-warning fade show"><i class="fas fa-robot me-2"></i> Captcha validation failed.</div>';
        } else {
            // 1. Enregistrement BDD (On garde cette partie, c'est une bonne sécurité)
            $stmt = mysqli_prepare($connect, "INSERT INTO messages (name, email, content, viewed, created_at) VALUES (?, ?, ?, 'No', NOW())");
            $full_content = "Subject: $subject\n\n$message";
            mysqli_stmt_bind_param($stmt, "sss", $name, $email, $full_content);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // 2. Envoi Email via SMTP (PHPMailer)
            // On utilise la fonction send_email() définie dans functions.php
            
            $to = $settings['email']; 
            $email_subject = "[" . $settings['sitename'] . "] New message: $subject";
            
            // Corps du message en HTML
            $email_body = "
                <h3>New message from your contact form</h3>
                <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                <hr>
                <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>
                <br>
                <small>Sent from " . $settings['sitename'] . "</small>
            ";
            
            // Corps alternatif (Texte brut) pour les clients mail anciens
            $alt_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";

            // Appel de la fonction SMTP
            // Note: send_email($to, $subject, $body, $altBody)
            $mail_result = send_email($to, $email_subject, $email_body, $alt_body);

            if ($mail_result['success']) {
                $success = '<div class="alert alert-success fade show"><i class="fas fa-check-circle me-2"></i> Your message has been sent successfully. We will respond promptly.</div>';
                // On vide les champs seulement si succès
                $name = $email = $subject = $message = ""; 
            } else {
                // On affiche l'erreur technique pour le debug (utile pour vous)
                // En production, vous pouvez masquer $mail_result['message'] et mettre un message générique
                $success = '<div class="alert alert-warning fade show"><i class="fas fa-check-circle me-2"></i> Your message has been saved in our database, but the email notification failed. Error: ' . htmlspecialchars($mail_result['message']) . '</div>';
            }
        }
    }
}
?>

    <div class="col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4 p-md-5">
                <h2 class="fw-bold mb-4 text-primary"><i class="fas fa-paper-plane me-2"></i> Contact Us</h2>
                <p class="text-muted mb-4">Any questions? Suggestions? Feel free to write to us using the form below.</p>

                <?php echo $error; ?>
                <?php echo $success; ?>

                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="name" name="name" placeholder="Your Name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                <label for="name"><i class="fas fa-user me-1"></i> Full Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                                <label for="email"><i class="fas fa-envelope me-1"></i> Email Address</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required>
                        <label for="subject"><i class="fas fa-tag me-1"></i> Subject</label>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea class="form-control" placeholder="Your message here" id="message" name="message" style="height: 150px" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        <label for="message"><i class="fas fa-comment-alt me-1"></i> Your Message</label>
                    </div>

                    <?php if (!empty($settings['gcaptcha_sitekey'])): ?>
                        <div class="mb-4 d-flex justify-content-center">
                            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($settings['gcaptcha_sitekey']); ?>"></div>
                        </div>
                    <?php endif; ?>

                    <div class="d-grid">
                        <button type="submit" name="send" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            Send the message <i class="fas fa-long-arrow-alt-right ms-2"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="card-body p-4">
                <h4 class="fw-bold mb-4"><i class="fas fa-info-circle me-2"></i> Information</h4>
                
                <div class="mb-4">
                    <h6 class="text-uppercase opacity-75 small fw-bold">Email</h6>
                    <a href="mailto:<?php echo htmlspecialchars($settings['email']); ?>" class="text-white text-decoration-none fs-5">
                        <?php echo htmlspecialchars($settings['email']); ?>
                    </a>
                </div>

                <?php if($settings['facebook'] || $settings['twitter'] || $settings['instagram'] || $settings['youtube']): ?>
                <div class="mb-0">
                    <h6 class="text-uppercase opacity-75 small fw-bold mb-3">Follow Us</h6>
                    <div class="d-flex gap-2">
                        <?php if($settings['facebook']): ?>
                            <a href="<?php echo htmlspecialchars($settings['facebook']); ?>" class="btn btn-light btn-sm rounded-circle text-primary" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if($settings['twitter']): ?>
                            <a href="<?php echo htmlspecialchars($settings['twitter']); ?>" class="btn btn-light btn-sm rounded-circle text-info" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if($settings['instagram']): ?>
                            <a href="<?php echo htmlspecialchars($settings['instagram']); ?>" class="btn btn-light btn-sm rounded-circle text-danger" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                         <?php if($settings['youtube']): ?>
                            <a href="<?php echo htmlspecialchars($settings['youtube']); ?>" class="btn btn-light btn-sm rounded-circle text-danger" style="width:35px; height:35px; display:flex; align-items:center; justify-content:center;"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm border-0 overflow-hidden">
            <?php 
            // Décoder le code map stocké
            $map_code = base64_decode($settings['google_maps_code']);
            
            if (!empty($map_code)): 
                // Petite astuce : on force width="100%" pour que ça s'adapte à la colonne
                $map_code = preg_replace('/width="\d+"/', 'width="100%"', $map_code);
                $map_code = preg_replace('/height="\d+"/', 'height="250"', $map_code); // Hauteur fixe
                echo $map_code;
            else: 
            ?>
                <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 250px;">
                    <div class="text-center">
                        <i class="fas fa-map-marker-alt fa-3x mb-2 opacity-50"></i>
                        <p class="mb-0 small">Map not configured</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- div class="card shadow-sm border-0 overflow-hidden">
            <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 250px;">
                <div class="text-center">
                    <i class="fas fa-map-marker-alt fa-3x mb-2 opacity-50"></i>
                    <p class="mb-0 small">Carte Google Maps</p>
                    </div>
            </div>
        </div -->

    </div>

<?php
// Le footer ferme la row et le container ouverts dans head()
footer();
?>
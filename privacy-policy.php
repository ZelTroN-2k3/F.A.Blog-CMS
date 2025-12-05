<?php
include "core.php";
head();

if ($settings['sidebar_position'] == 'Left') {
	sidebar();
}
?>

<div class="col-md-8 mb-4">
    <div class="card shadow-sm border-0">
        
        <div class="card-header bg-primary text-white p-3">
            <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i> Privacy Policy</h4>
        </div>

        <div class="card-body p-4">
            
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">Who are we?</h5>
                <p>
                    See the <a href="legal-notice" class="text-primary">legal notice</a> of this site, to find out who we are, and learn more about the use and protection of your data.
                </p>
            </div>
            
            <hr class="my-4 opacity-25">

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">Use of collected personal data</h5>
                
                <h6 class="fw-bold mt-4">Comments</h6>
                <p>
                    When you leave a comment on our website, the data entered in the comment form, but also your IP address and browser user agent are collected to help us detect unwanted comments.
                </p>
                <p>
                    An anonymized string created from your email address (also called a hash) may be sent to the Gravatar service to verify if you are using it. The Gravatar service privacy clauses are available here: https://automattic.com/privacy/. After validation of your comment, your profile picture will be visible publicly next to your comment.
                </p>

                <h6 class="fw-bold mt-4">Media</h6>
                <p>
                    If you are a registered user and upload images to the website, we advise you to avoid uploading images containing EXIF GPS coordinate data. Visitors to your website can download and extract location data from these images.
                </p>

                <h6 class="fw-bold mt-4">Contact Forms</h6>
                <p>
                    When you send us a message from <a href="contact" class="text-primary">the contact page of our website</a>, the data entered in the sending form is transmitted to us by email.
                </p>

                <h6 class="fw-bold mt-4">Cookies</h6>
                <p>
                    If you leave a comment on our site, you will be offered to save your name, email address and website in cookies. This is only for your convenience so that you do not have to enter this information if you leave another comment later. These cookies expire after one year.
                </p>
                <p>
                    If you go to the login page, a temporary cookie will be created to determine if your browser accepts cookies. It contains no personal data and will be deleted automatically when you close your browser.
                </p>
                <p>
                    When you log in, we will set up a number of cookies to save your login information and your screen preferences. The lifetime of a login cookie is two days, that of a screen option cookie is one year. If you check "Remember me", your login cookie will be kept for two weeks. If you log out of your account, the login cookie will be deleted.
                </p>
                <p>
                    By editing or publishing a post, an additional cookie will be saved in your browser. This cookie includes no personal data. It simply indicates the ID of the post you just edited. It expires after one day.
                </p>

                <h6 class="fw-bold mt-4">Embedded content from other sites</h6>
                <p>
                    Articles on this site may include embedded content (e.g. videos, images, articles...). Embedded content from other sites behaves in the exact same way as if the visitor visited that other site.
                </p>
                <p>
                    These websites could collect data about you, use cookies, embed third-party tracking tools, track your interactions with this embedded content if you have a connected account on their website.
                </p>

                <h6 class="fw-bold mt-4">Statistics and audience measurements</h6>
                <p>
                    For more information on cookies, see the <a href="legal-notice#cookies" class="text-primary">Cookies section</a> of our legal notice.
                </p>
            </div>

            <hr class="my-4 opacity-25">

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">Use and transmission of your personal data</h5>

                <h6 class="fw-bold mt-4">Storage durations of your data</h6>
                <p>
                    If you leave a comment, the comment and its metadata are retained indefinitely. This allows recognizing and approving automatically follow-up comments instead of leaving them in the moderation queue.
                </p>
                <p>
                    For users who register on our site (if possible), we also store the personal data indicated in their profile. All users can see, modify or delete their personal information at any time (except their username). Site managers can also see and modify this information.
                </p>

                <h6 class="fw-bold mt-4">The rights you have over your data</h6>
                <p>
                    If you have an account or have left comments on the site, you can request to receive a file containing all the personal data we hold about you, including those you have provided to us. You can also request the deletion of personal data concerning you. This does not take into account data stored for administrative, legal or security purposes.
                </p>

                <h6 class="fw-bold mt-4">Transmission of your personal data</h6>
                <p>
                    Visitor comments may be checked through an automated spam detection service.
                </p>
                <p>
                    For more information, do not hesitate to consult our <a href="legal-notice" class="text-primary">legal notice</a>.
                </p>
            </div>

        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>
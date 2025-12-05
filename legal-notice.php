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
            <h4 class="mb-0"><i class="fas fa-balance-scale me-2"></i> Legal Notice</h4>
        </div>

        <div class="card-body p-4">
            
            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="le-site">THE SITE</h5>
                <p>
                    The site "freelance-addons.net" is available at the following address: <a href="https://freelance-addons.net/" class="text-primary">https://freelance-addons.net/</a>.
                    To contact us, please <a href="contact" class="text-primary">click here</a>.
                </p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="la-societe-editeur-du-site">THE COMPANY (Site Publisher)</h5>
                <p>
                    The website freelance-addons.net is the property of the company <strong>****</strong>, registered with the RCS Libourne under number *** *** *** ****, whose registered office is located at (00 rue de la pays 00000 DANS CE PAYS).<br>
                    <strong>Manager: Patrick ANCHER</strong>.
                </p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="contact">CONTACT</h5>
                <p>To report abuse, <a href="contact" class="text-primary">write here</a> mentioning the following information:</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0"><i class="fas fa-check text-success me-2"></i> The URL of the problematic page</li>
                    <li class="list-group-item px-0"><i class="fas fa-check text-success me-2"></i> Copy of the passage or link posing a problem</li>
                    <li class="list-group-item px-0"><i class="fas fa-check text-success me-2"></i> The justification for your removal request</li>
                    <li class="list-group-item px-0"><i class="fas fa-check text-success me-2"></i> Your name, first name, physical address, and email address</li>
                </ul>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="hebergement-web">WEB HOSTING</h5>
                <p>The site freelance-addons.net is hosted by the French company <a href="https://www.o2switch.fr/" target="_blank" rel="nofollow noopener" class="text-primary">o2switch</a>:</p>
                <div class="alert alert-light border-start border-primary border-4">
                    <strong>O2SWITCH</strong><br>
                    222-224 Boulevard Gustave Flaubert<br>
                    63000 Clermont-Ferrand<br><br>
                    SARL with a capital of €100,000<br>
                    Siret: 510 909 80700024<br>
                    RCS Clermont-Ferrand
                </div>
            </div>

            <hr class="my-4 opacity-25">

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">OBJECTIVE AND QUALITY OF CONTENT</h5>
                <p>
                    The main goal of freelance-addons.net is to disseminate information on electronics to the general public. 
                    The themes addressed relate to what the author is passionate about, without aiming to be complete or exhaustive. 
                    Here, the author seeks to share as much knowledge, experience, and discoveries as possible about electronics and everything surrounding it.
                </p>
                <p>
                    However, as the author is neither an engineer, nor a teacher, nor an expert in electronics, inaccuracies may appear. 
                    We encourage all readers to exchange with the author at the bottom of each article, noting any errors or inaccuracies, in order to perpetually improve the content of this site.
                </p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">PRIVACY PROTECTION</h5>
                <p>
                    Security and the protection of your personal data are important to us. We are committed to ensuring that the collection and processing of your data, carried out from the site freelance-addons.net, comply with the Data Protection Act of January 6, 1978.
                </p>
                <p>The contact form and the comment areas at the bottom of each article on this site are limited to the strict minimum regarding the collection of personal data.</p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">GDPR</h5>
                <p>The site freelance-addons.net is in line with Regulation (EU) 2016/679 of the European Parliament and of the Council of April 27, 2016. Our commitment:</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0"><i class="fas fa-user-lock text-primary me-2"></i> Limit data collection to the strict necessary (name and email address)</li>
                    <li class="list-group-item px-0"><i class="fas fa-user-lock text-primary me-2"></i> Obtain and keep user consent (personal data is respected and protected)</li>
                    <li class="list-group-item px-0"><i class="fas fa-user-lock text-primary me-2"></i> Secure collected data (the site is perfectly secured: firewall, https, anti-spam...)</li>
                </ul>
                <p>To know everything about your rights and this European regulation, <a href="https://www.cnil.fr/fr/reglement-europeen-protection-donnees" rel="nofollow noopener" target="_blank" class="text-primary">click here</a>.</p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="cookies">COOKIES</h5>
                <p>
                    When consulting the site freelance-addons.net, cookies are deposited on your computer, tablet, or smartphone.
                    This page allows you to better understand how cookies work and how to use current tools to configure them.
                </p>
                
                <h6 class="fw-bold mt-3">What is a Cookie?</h6>
                <p>A cookie is a small text file that may be placed on your terminal when consulting a website. A cookie file allows its issuer to identify the terminal in which it is registered, during the validity or registration period of said cookie. Some cookies are essential for the use of the site, others allow optimizing and customizing the displayed content.</p>

                <h6 class="fw-bold mt-3">Who deposits Cookies?</h6>
                <p>Cookies placed on freelance-addons.net can be deposited by freelance-addons.net or by third parties. Cookies deposited by freelance-addons.net are essentially those related to the operation of the site. Others are third-party cookies deposited by our partners.</p>
                <p>The issue and use of cookies by third parties are subject to the "cookie" policies of these third parties. Only the issuer of a cookie is likely to read the information contained therein. We inform you of the purpose of the third-party cookies of which we are aware and the means at your disposal to make choices regarding these cookies.</p>

                <h6 class="fw-bold mt-3">Cookies necessary for the proper functioning of the site</h6>
                <p>These are cookies essential for navigation on freelance-addons.net (such as session identifiers) which allow you to use the main features of the site and secure your connection. Without these cookies, you will not be able to use the site normally. We advise against deleting them.</p>
                <ul>
                    <li>PHPSESSID: PHP session cookie</li>
                    <li>wordpress_*: Session cookies used by WordPress</li>
                    <li>wp-settings*: WordPress customization cookies</li>
                </ul>

                <h6 class="fw-bold mt-3">Audience and statistics cookies</h6>
                <p>These cookies allow us to know the use and performance of the site freelance-addons.net, to establish statistics, volumes of attendance and use of the various elements of this site (contents visited, routes), allowing us to improve the interest and ergonomics of our services (pages or sections most often consulted, most read articles...). Cookies are also used to count visitors to a page. These are cookies from Google Analytics.</p>

                <h6 class="fw-bold mt-3">Google Cookies</h6>
                <p>Google Analytics is a web analytics service provided by Google Inc. Data generated by cookies regarding your use of the site (including your IP address) will be transmitted and stored by Google on servers located in the United States. Google will use this information for the purpose of evaluating your use of the site, compiling reports on site activity for its publisher and providing other services relating to site activity and Internet usage.</p>
                <p>Google is likely to communicate this data to third parties in the event of a legal obligation or where these third parties process this data on behalf of Google, including in particular the publisher of this site. Google will not cross-check your IP address with any other data held by Google. You can disable the use of cookies by selecting the appropriate settings on your browser.</p>
                <p>However, such deactivation could prevent the use of certain features of this site. By using this website, you expressly consent to the processing of your personal data by Google under the conditions and for the purposes described above.</p>

                <h6 class="fw-bold mt-3">Analytics Cookies</h6>
                <ul>
                    <li>_ga, _gat: Cookies specific to Google Analytics (statistics)</li>
                </ul>
                <p>More information on the use of cookies by Google:</p>
                <ul>
                    <li><a href="https://www.google.com/policies/technologies/cookies/" rel="nofollow noopener" target="_blank" class="text-primary">How Google uses cookies</a></li>
                    <li><a href="https://www.google.com/policies/technologies/ads/" rel="nofollow noopener" target="_blank" class="text-primary">How Google uses advertising cookies</a></li>
                </ul>

                <h6 class="fw-bold mt-3">How to configure your choices?</h6>
                <p>You can configure the deposit of cookies through your Internet browser. The setting made through this means is also modifiable at any time.</p>
                <p>Depending on the type of browser, you have the following options: accept or reject cookies from any origin or from a given source or program the display of a message asking for your agreement each time a cookie is deposited on your terminal.</p>
                <p>To express or return to your choices, refer to the help menu or the dedicated section of your browser (Microsoft Edge, Safari, Chrome, Firefox, Opera).</p>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> We draw your attention to the fact that cookies improve your browsing comfort on this site and allow you to access certain secure areas. If you decide to block all cookies via your internet browser, you will only be able to visit the public part of the site. Depending on your browser type, you can also activate "Private Browsing" mode or "Do Not Track" setting.
                </div>
            </div>

            <hr class="my-4 opacity-25">

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3">COMMENTS PUBLISHED BY USERS OF THIS SITE</h5>
                <p>
                    Each user of the site freelance-addons.net has the possibility to publish opinions, information, and data via comments, accessible on certain pages of this site.
                    These comments reflect exclusively the point of view of the person who publishes them, and not necessarily ours.
                    We cannot be held responsible for the content of these comments, or for any loss, cost, damage, or expense caused, suffered, or related to the use or publication of comments.
                </p>
                <p>
                    We reserve the right to monitor all comments, and to modify or delete any that we deem, in our sole discretion, to be inappropriate, unrelated to the subject where it is published, offensive, or in violation in any way with these legal notices.
                    This is why each new comment on this site will first go through a prior waiting phase, before potential dissemination.
                    On this occasion, each comment may be modified and corrected, particularly in terms of its formatting, spelling, conjugation, and/or grammar.
                </p>
                <p>Note that the comment area is not a forum, and is rather reserved for exchanges with the authors publishing on this site, about the subject and/or content of their publications.</p>
                <p>As a user of this site, you warrant and declare that:</p>
                <ul class="list-group list-group-flush mb-3">
                    <li class="list-group-item px-0"><i class="fas fa-check text-primary me-2"></i> You have all rights, licenses, and consents to post your content on this site</li>
                    <li class="list-group-item px-0"><i class="fas fa-check text-primary me-2"></i> The comment does not infringe any intellectual property, in particular copyright, patent, or trademark, or any other intellectual property belonging to a third party</li>
                    <li class="list-group-item px-0"><i class="fas fa-check text-primary me-2"></i> The comment contains no defamatory, libelous, offensive, indecent, or illegal part, nor an invasion of privacy</li>
                    <li class="list-group-item px-0"><i class="fas fa-check text-primary me-2"></i> The comment will not be used to solicit a business relationship or advertise a commercial or illegal activity</li>
                </ul>
                <p>Hereby, you grant us a non-exclusive and royalty-free right to use, reproduce, modify, and authorize third parties to do the same on your comments, in any form, format, or medium.</p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="affiliation">AFFILIATION / SPONSORING</h5>
                <p>
                    In order to finance in particular the purchase of electronic components, the manufacture of products, and the acquisition of tools allowing to realize or enrich content on this site, the latter participates in several affiliate programs (such as Aliexpress, Kaiweets, ...).
                    When possible, links are affiliated. This induces remuneration when a product or service is sold through this means.
                    The user remains of course free to search for products and services by themselves, if they prefer not to go through the links presented on freelance-addons.net.
                </p>
                <!--p>
                    Since July 2025, the site freelance-addons.net is sponsored by PCBWay, for the manufacture of printed circuits and stencils; which allows the publisher to give feedback on the quality of these services.
                    In return, the site freelance-addons.net provides links to PCBWay, intended for visitors, if they wish to reproduce the site's printed circuits, or other.
                    Of course, users of the site remain free to choose whether or not to follow these links, or to opt for other manufacturers (such as JLCPCB or Eurocircuits, for example).
                </p-->
                <p>Note that certain products highlighted on freelance-addons.net may be subject to editing or deletion at any time.</p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="responsabilite-de-l-editeur">PUBLISHER'S LIABILITY</h5>
                <p>
                    The site freelance-addons.net contains external links to other websites, which the publisher does not operate.
                    And although the publisher constantly monitors the quality of links, he cannot in any way be held responsible for the provision of these links, allowing access to these external sites and sources, and cannot bear any responsibility for the content, advertisements, products, services or any other material available on or from these external sites or sources, which are neither verified nor approved by the publisher.
                </p>
                <p>The publisher declines all responsibility for the quality and accuracy of the information or data of which the user may have become aware through the site freelance-addons.net, whether in the form of information, text link, search result, or advertisement.</p>
                <p>The user of this site acknowledges that all site content is for information purposes only, and in no case recommendations. Also, in no case can the publisher be held responsible for any damage, direct or indirect, which would result from the use of the site or the content present on freelance-addons.net.</p>
            </div>

            <div class="bg-light p-3 rounded border mb-4">
                <h5 class="fw-bold mb-3">INTELLECTUAL PROPERTY AND COUNTERFEITING</h5>
                <p>The site freelance-addons.net is the owner of the intellectual property rights or holds the usage rights on all elements accessible on the site, notably texts, images, graphics, logo, icons, sounds, software.</p>
                <p>Any reproduction, representation, modification, publication, adaptation of all or part of the elements of the sites, whatever the means or process used, is prohibited, except with prior written authorization from the site manager. Any violation may result in legal proceedings and account deletion without notice.</p>
                <p class="mb-0 small text-muted">
                    Any unauthorized exploitation of the contents of the site freelance-addons.net will be considered as constituting an infringement and prosecuted in accordance with the provisions of <a href="https://www.legifrance.gouv.fr/affichCodeArticle.do?cidTexte=LEGITEXT000006069414&amp;idArticle=LEGIARTI000006279172" target="_blank" rel="nofollow noopener" class="text-secondary">Article L.335-2 of the Intellectual Property Code</a>.
                </p>
            </div>

            <div class="mb-4">
                <h5 class="fw-bold text-primary mb-3" id="mise-a-jour">UPDATE</h5>
                <p>We reserve the right to add, modify, and update our terms of use without prior notice to you. Any change to our terms is effective immediately. It is your responsibility to read this page to ensure you agree with our terms.</p>
            </div>
            
            <p class="text-end text-muted mt-3 small"><em>Page updated on 07/23/2025</em></p>

        </div>
    </div>
</div>

<?php
if ($settings['sidebar_position'] == 'Right') {
	sidebar();
}
footer();
?>
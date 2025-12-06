/*
 * Script pour les interactions de la page post.php
 * (Partage, Commentaires, Likes, Favoris)
 */
document.addEventListener('DOMContentLoaded', function () {
  // --- Données dynamiques ---
  const commentFormContainer = document.getElementById(
    'comment-form-container'
  );

  // Si le conteneur n'existe pas, ne rien faire (on n'est pas sur post.php)
  if (!commentFormContainer) {
    return;
  }

  // Récupérer les données dynamiques stockées dans le HTML
  const dynamicPostId = commentFormContainer.dataset.postId;
  const isGuestCommenting =
    commentFormContainer.dataset.isGuestCommenting === 'true';

  // --- Initialisation des partages sociaux ---
  if (typeof $ !== 'undefined' && typeof $.fn.jsSocials !== 'undefined') {
    $('#share').jsSocials({
      showCount: false,
      showLabel: true,
      shares: [
        { share: 'facebook', logo: 'fab fa-facebook-square', label: 'Share' },
        { share: 'twitter', logo: 'fab fa-twitter-square', label: 'Tweet' },
        { share: 'linkedin', logo: 'fab fa-linkedin', label: 'Share' },
        { share: 'email', logo: 'fas fa-envelope', label: 'E-Mail' },
      ],
    });
  }

  // --- Constantes pour les commentaires ---
  const mainForm = document.getElementById('main-comment-form');
  const parentIdInput = document.getElementById('parent_id');
  const cancelBtn = document.getElementById('cancel-reply-btn');
  const formTitle = commentFormContainer.querySelector('h5.leave-comment-title');
  const formMessages = document.getElementById('comment-form-messages');
  const submitBtn = document.getElementById('submit-comment-btn');
  const commentListContainer = document.getElementById('comment-list-container');
  const commentsCount = document.getElementById('comments-count');
  const originalFormParent = commentFormContainer.parentNode;
  const commentTextarea = document.getElementById('comment');

  // --- Fonction de comptage de caractères ---
  if (commentTextarea) {
    commentTextarea.addEventListener('input', function () {
      const text = this.value;
      const charCount = document.getElementById('characters');
      if (charCount) {
        charCount.innerText = 1000 - text.length;
      }
    });
    // Trigger une fois au chargement
    commentTextarea.dispatchEvent(new Event('input'));
  }

  // --- Fonctions pour les commentaires ---

  // Fonction pour mettre à jour le compteur global de commentaires
  function updateGlobalCommentCount() {
    if (commentsCount) {
      // Requête pour récupérer le nouveau compte total
      fetch(
        `ajax_submit_comment.php?action=get_count&post_id=${dynamicPostId}`
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.count !== undefined) {
            commentsCount.innerText = data.count;
          }
        })
        .catch((error) =>
          console.error('Error fetching comment count:', error)
        );
    }
  }

  // Fonction pour répondre à un commentaire
  window.replyToComment = function (commentId) {
    const commentElement = document.getElementById('comment-' + commentId);
    if (!commentElement) return;

    // Annuler la réponse précédente
    cancelReply();

    commentElement.appendChild(commentFormContainer);
    parentIdInput.value = commentId;
    cancelBtn.style.display = 'block';
    formTitle.innerHTML =
      '<i class="fas fa-reply"></i> Replying to comment #' + commentId;
    if (commentTextarea) {
      commentTextarea.focus();
    }
  };

  // Fonction pour annuler la réponse
  window.cancelReply = function () {
    originalFormParent.appendChild(commentFormContainer);
    parentIdInput.value = '0';
    cancelBtn.style.display = 'none';
    formTitle.innerHTML = '<i class="fas fa-reply"></i> Leave A Comment';
    formMessages.innerHTML = '';
    if (mainForm) {
      mainForm.reset();
    }
    const charCount = document.getElementById('characters');
    if (charCount) {
      charCount.innerText = '1000';
    }

    if (isGuestCommenting) {
      if (typeof grecaptcha !== 'undefined') {
        grecaptcha.reset();
      }
    }
  };

  // --- GESTION AJAX (Soumission du commentaire) ---
  if (mainForm) {
    mainForm.addEventListener('submit', function (e) {
      e.preventDefault();

      submitBtn.value = 'Sending...';
      submitBtn.disabled = true;
      formMessages.innerHTML = '';

      const formData = new FormData(mainForm);

      fetch('ajax_submit_comment.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            
            // --- MODIFICATION ICI : Gestion de la modération ---
            if (data.moderation === true) {
                // CAS 1 : En attente de modération (Message JAUNE)
                formMessages.innerHTML = '<div class="alert alert-warning"><i class="fas fa-clock"></i> ' + data.message + '</div>';
            
            } else {
                // CAS 2 : Publié directement (Message VERT)
                formMessages.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + data.message + '</div>';
                
                // Insertion du HTML du nouveau commentaire
                if (data.html) {
                  const tempDiv = document.createElement('div');
                  tempDiv.innerHTML = data.html;
                  const newCommentElement = tempDiv.firstElementChild;
    
                  if (data.parent_id == 0) {
                    commentListContainer.appendChild(newCommentElement);
                    const noCommentsAlert = document.getElementById('no-comments-alert');
                    if (noCommentsAlert) noCommentsAlert.style.display = 'none';
                  } else {
                    const parentElement = document.getElementById('comment-' + data.parent_id);
                    if (parentElement) {
                      parentElement.appendChild(newCommentElement);
                    }
                  }
    
                  // Animer l'apparition
                  setTimeout(() => {
                    if (newCommentElement) {
                      newCommentElement.style.opacity = 1;
                      newCommentElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                      });
                    }
                  }, 10);
    
                  // Mettre à jour le compteur total
                  updateGlobalCommentCount();
                }
            }
            // --- FIN MODIFICATION ---

            mainForm.reset();
            const charCount = document.getElementById('characters');
            if (charCount) {
              charCount.innerText = '1000';
            }

            // Réinitialiser reCAPTCHA si c'est un invité
            if (isGuestCommenting) {
              if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
              }
            }

            // Réinitialiser et déplacer le formulaire (sauf si on veut laisser le msg de modération visible à cet endroit)
            // On attend un peu pour que l'utilisateur voie le message avant de reset le formulaire
            if(data.moderation !== true) {
                cancelReply();
            } else {
                // Si modération, on remet juste le parentId à 0 mais on laisse le message affiché
                 parentIdInput.value = '0';
            }
            
          } else {
            // ERREUR (Message ROUGE)
            formMessages.innerHTML =
              '<div class="alert alert-danger">' + data.message + '</div>';

            // Réinitialiser reCAPTCHA pour que l'utilisateur puisse réessayer
            if (isGuestCommenting) {
              if (typeof grecaptcha !== 'undefined') {
                grecaptcha.reset();
              }
            }
          }
        })
        .catch((error) => {
          console.error('Error:', error);
          formMessages.innerHTML =
            '<div class="alert alert-danger">A network error has occurred.</div>';
        })
        .finally(() => {
          submitBtn.value = 'Post';
          submitBtn.disabled = false;
        });
    });
  }

  // --- GESTION DU "LIKE" ---
  const likeButton = document.getElementById('like-button');
  if (likeButton) {
    likeButton.addEventListener('click', function () {
      const likeText = document.getElementById('like-text');
      const likeCount = document.getElementById('like-count');

      likeButton.disabled = true;

      const formData = new FormData();
      formData.append('post_id', this.dataset.postId);

      fetch('ajax_like_post.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Mettre à jour le compteur
            likeCount.innerText = data.new_count;

            // Mettre à jour l'apparence du bouton
            if (data.liked) {
              likeButton.classList.remove('btn-outline-primary');
              likeButton.classList.add('btn-primary');
              likeText.innerText = 'Liked';
            } else {
              likeButton.classList.remove('btn-primary');
              likeButton.classList.add('btn-outline-primary');
              likeText.innerText = 'Like';
            }
          } else {
            console.error(data.message);
          }
        })
        .catch((error) => console.error('Erreur:', error))
        .finally(() => {
          likeButton.disabled = false;
        });
    });
  }

  // --- GESTION DU "FAVORI" ---
  const favButton = document.getElementById('favorite-button');
  if (favButton) {
    favButton.addEventListener('click', function () {
      const favText = document.getElementById('favorite-text');
      const favIcon = this.querySelector('i');

      favButton.disabled = true;

      const formData = new FormData();
      formData.append('post_id', this.dataset.postId);

      fetch('ajax_favorite_post.php', {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Mettre à jour l'apparence du bouton
            if (data.favorited) {
              favButton.classList.remove('btn-outline-warning');
              favButton.classList.add('btn-warning');
              favIcon.classList.remove('far');
              favIcon.classList.add('fas');
              favText.innerText = 'Saved';
            } else {
              favButton.classList.remove('btn-warning');
              favButton.classList.add('btn-outline-warning');
              favIcon.classList.remove('fas');
              favIcon.classList.add('far');
              favText.innerText = 'Save';
            }
          } else {
            console.error(data.message);
            alert(data.message);
          }
        })
        .catch((error) => console.error('Erreur:', error))
        .finally(() => {
          favButton.disabled = false;
        });
    });
  }

  // --- Initialisation de Highlight.js ---
  if (typeof hljs !== 'undefined') {
    hljs.highlightAll();
  }
});
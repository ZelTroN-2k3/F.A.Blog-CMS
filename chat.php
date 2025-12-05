<?php
include "core.php";
head();

if ($logged != 'Yes') {
    echo '<script>window.location="login.php";</script>';
    exit;
}
?>

<link rel="stylesheet" href="assets/css/chat.css?v=<?php echo time(); ?>">

<div class="container mt-4 mb-5">
    <div class="chat-wrapper" id="chatWrapper">
        
        <div class="chat-sidebar">
            
            <div class="chat-sidebar-top">
                
                <div class="position-relative mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 rounded-end-pill bg-white" id="userSearchInput" placeholder="Rechercher...">
                    </div>
                    <div class="search-results shadow-sm" id="searchResults"></div>
                </div>

                <ul class="nav nav-pills nav-fill gap-1" id="chatTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="online-tab" data-bs-toggle="tab" data-bs-target="#online-content" type="button" role="tab" title="Utilisateurs en ligne">
                            <i class="fas fa-circle small"></i>
                        </button>
                    </li>

                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="chats-tab" data-bs-toggle="tab" data-bs-target="#chats-content" type="button" role="tab" title="Discussions">
                            <i class="fas fa-comments small"></i>
                        </button>
                    </li>
                    
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="calls-tab" data-bs-toggle="tab" data-bs-target="#calls-content" type="button" role="tab" title="Appels">
                            <i class="fas fa-phone small"></i>
                        </button>
                    </li>
                    
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="status-tab" data-bs-toggle="tab" data-bs-target="#status-content" type="button" role="tab" title="Statut">
                            <i class="fas fa-chart-bar small"></i>
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="chat-sidebar-content tab-content" id="chatTabsContent">
                
                <div class="tab-pane fade show active h-100" id="online-content" role="tabpanel">
                    <div class="chat-list" id="onlineList">
                        <div class="text-center mt-4 text-muted small">Loading...</div>
                    </div>
                </div>
                
                <div class="tab-pane fade h-100" id="chats-content" role="tabpanel">
                    <div class="chat-list" id="conversationsList">
                        <div class="text-center mt-5"><div class="spinner-border text-primary" role="status"></div></div>
                    </div>
                </div>

                <div class="tab-pane fade h-100" id="calls-content" role="tabpanel">
                    <div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted p-4 text-center">
                        <div class="bg-light rounded-circle p-4 mb-3"><i class="fas fa-phone-slash fa-2x"></i></div>
                        <h6>Aucun appel récent</h6>
                    </div>
                </div>

                <div class="tab-pane fade h-100" id="status-content" role="tabpanel">
                    <div class="chat-item cursor-pointer" onclick="openStatusModal()">
                        <div class="position-relative">
                            <img src="<?php echo htmlspecialchars($rowu['avatar']); ?>" class="rounded-circle" style="width:45px; height:45px; object-fit:cover;">
                            <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 16px; height: 16px; font-size:10px;"><i class="fas fa-plus"></i></span>
                        </div>
                        <div class="chat-info ms-3">
                            <div class="chat-name">Mon statut</div>
                            <div class="chat-preview">Ajouter une actualité</div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="chat-sidebar-footer">
                <div class="chat-date-separator my-0"><span style="background:#fff; border:1px solid #eee;">Dossiers</span></div>
                
                <a href="#" class="chat-footer-link" onclick="openArchivedModal()">
                    <i class="fas fa-archive"></i> Discussions archivées
                </a>
                <a href="#" class="chat-footer-link" onclick="openStarredModal()">
                    <i class="fas fa-star"></i> Messages importants
                </a>
            </div>

        </div>

        <div class="chat-main">
            
            <div class="drag-overlay">
                <i class="fas fa-cloud-upload-alt fa-5x mb-3"></i>
                <h3>Drop image here to send</h3>
            </div>

            <div class="chat-main-header" id="chatHeader" style="visibility: hidden;">
                <i class="fas fa-arrow-left back-btn text-secondary" onclick="closeChatMobile()"></i>
                <img src="assets/img/avatar.png" id="chatHeaderAvatar" class="rounded-circle" width="40" height="40" style="object-fit: cover; margin-right: 10px;">
                <div>
                    <div class="chat-name" id="chatHeaderName">User</div>
                    <div class="text-muted small" id="chatHeaderStatus" style="font-size: 0.75rem;"></div>
                </div>
            </div>

            <div class="chat-messages-area" id="messageArea">
                <div class="h-100 d-flex align-items-center justify-content-center text-muted flex-column">
                    <i class="fab fa-whatsapp fa-4x mb-3 text-success opacity-50"></i>
                    <h4>Welcome to Chat</h4>
                    <p>Select a conversation to start messaging.</p>
                </div>
            </div>

            <div class="image-preview-container" id="imgPreviewArea">
                <div class="image-preview-wrapper">
                    <img src="" id="imgPreviewTag" class="preview-img">
                    <div class="remove-img-btn" onclick="removeImage()">x</div>
                </div>
                <small class="text-muted ms-2">Image selected</small>
            </div>

            <div class="chat-input-area" id="inputArea" style="display:none;">
                <form id="sendMessageForm" class="w-100 d-flex align-items-center" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="other_user_id" id="currentOtherUserId" value="">
                    <input type="hidden" name="action" value="send_message">
                    
                    <label for="imageInput" class="btn text-secondary mb-0 me-1" style="cursor: pointer;" title="Send Image">
                        <i class="fas fa-image fa-lg"></i>
                    </label>
                    <input type="file" name="image" id="imageInput" accept="image/*" style="display: none;">

                    <button type="button" id="emojiBtn" class="btn text-secondary me-1">
                        <i class="far fa-smile fa-lg"></i>
                    </button>

                    <textarea name="message" class="form-control" placeholder="Type a message..." id="msgInput" rows="1"></textarea>
                    
                    <button type="submit" class="btn text-primary ms-2"><i class="fas fa-paper-plane fa-lg"></i></button>
                </form>
            </div>
        </div>

    </div>
</div>

<div class="modal fade" id="archivedModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-archive text-muted me-2"></i> Discussions Archivées</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="archivedListBody">
          </div>
    </div>
  </div>
</div>

<div class="modal fade" id="starredModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-star text-warning me-2"></i> Messages Importants</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body bg-light" id="starredListBody">
          </div>
    </div>
  </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-circle-notch text-success me-2"></i> Ajouter un statut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <form id="statusForm" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="action" value="post_status">
              
              <div class="mb-3 text-center">
                  <label for="statusImageInput" class="btn btn-outline-secondary w-100" style="height: 150px; display: flex; align-items: center; justify-content: center; border-style: dashed;">
                      <div id="statusImagePreview">
                          <i class="fas fa-camera fa-2x mb-2"></i><br>Ajouter une photo
                      </div>
                  </label>
                  <input type="file" name="image" id="statusImageInput" hidden accept="image/*">
              </div>
              
              <div class="mb-3">
                  <textarea name="caption" class="form-control" placeholder="Ajouter une légende..." rows="2"></textarea>
              </div>
              
              <div class="d-grid">
                  <button type="submit" class="btn btn-success">Publier</button>
              </div>
          </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@3.1.1/dist/index.min.js"></script>

<script>
$(document).ready(function() {
    let currentChatId = null;
    let pollingInterval = null;
    let lastMsgCount = 0;

    // Fonction pour nettoyer l'URL de l'avatar (enlève les ../)
    function cleanAvatarPath(path) {
        if (!path) return 'assets/img/avatar.png';
        return path.replace('../', '');
    }

    // --- CHARGEMENT DES CONVERSATIONS ---
    function loadConversations() {
        $.post('ajax_chat.php', { action: 'fetch_conversations' }, function(res) {
            let html = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(function(conv) {
                    let activeClass = (currentChatId == conv.other_user_id) ? 'active' : '';
                    let unreadBadge = (conv.unread > 0) ? `<span class="badge bg-success rounded-pill ms-2">${conv.unread}</span>` : '';
                    
                    // Utilisation de la fonction de nettoyage d'avatar
                    let avatar = cleanAvatarPath(conv.avatar);
                    
                    html += `
                    <div class="chat-item ${activeClass}" onclick="openChat(${conv.other_user_id}, '${conv.username}', '${avatar}')">
                        <img src="${avatar}" alt="Avatar">
                        
                        <div class="chat-info">
                            <div class="d-flex justify-content-between">
                                <div class="chat-name">${conv.username}</div>
                                <div class="chat-meta">${conv.time_ago}</div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="chat-preview">${conv.last_msg}</div>
                                ${unreadBadge}
                            </div>
                        </div>

                        <div class="chat-item-actions">
                            <div class="btn-archive" onclick="event.stopPropagation(); toggleArchive(${conv.conv_id})" title="Archiver">
                                <i class="fas fa-archive"></i>
                            </div>
                        </div>

                    </div>`;
                });
            } else {
                html = '<div class="p-4 text-center text-muted small">No conversations yet.</div>';
            }
            $('#conversationsList').html(html);
        }, 'json');
    }
    
    // Initialisation
    loadConversations();
    loadOnlineUsers(); // <--- INDISPENSABLE : Charge la liste verte immédiatement
    setInterval(loadConversations, 5000);

    // --- CHARGEMENT ONLINE USERS ---
    function loadOnlineUsers() {
        if (!$('#online-tab').hasClass('active')) return;

        $.post('ajax_chat.php', { action: 'fetch_online_users' }, function(res) {
            let html = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(function(u) {
                    let avatar = (u.avatar && u.avatar !== '') ? u.avatar : 'assets/img/avatar.png';
                    html += `
                    <div class="chat-item" onclick="startNewChat(${u.id}, '${u.username}', '${avatar}')">
                        <div class="position-relative me-3">
                            <img src="${avatar}" alt="Avatar" class="rounded-circle" style="width:40px; height:40px; object-fit:cover;">
                            <span class="position-absolute bottom-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"><span class="visually-hidden">Online</span></span>
                        </div>
                        <div class="chat-info">
                            <div class="chat-name">${u.username}</div>
                            <div class="chat-preview text-success small">Online</div>
                        </div>
                        <button class="btn btn-sm btn-outline-primary rounded-circle"><i class="fas fa-paper-plane"></i></button>
                    </div>`;
                });
            } else {
                html = '<div class="p-4 text-center text-muted">No one else is online.</div>';
            }
            $('#onlineList').html(html);
        }, 'json');
    }

    $('#online-tab').on('shown.bs.tab', loadOnlineUsers);
    setInterval(loadOnlineUsers, 10000);

    // --- OUVERTURE D'UN CHAT ---
    window.openChat = function(otherUserId, username, avatar) {
        currentChatId = otherUserId;
        $('#chatWrapper').addClass('chat-active');
        $('#chatHeader').css('visibility', 'visible');
        $('#chatHeaderName').text(username);
        $('#chatHeaderAvatar').attr('src', avatar);
        $('#inputArea').show();
        $('#currentOtherUserId').val(otherUserId);
        
        window.shouldScroll = true;
        loadMessages();
        
        if(pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(loadMessages, 3000);
        checkUserStatus(); // Vérification immédiate du statut
    };

    // --- CHARGEMENT DES MESSAGES ---
    function loadMessages() {
        if(!currentChatId) return;
        
        $.post('ajax_chat.php', { action: 'fetch_messages', other_user_id: currentChatId }, function(res) {
            if(res.status === 'success') {
                let area = $('#messageArea');
                let isAtBottom = area.scrollTop() + area.innerHeight() >= area[0].scrollHeight - 50;
                
                // Détection nouveau message pour le son
                if (res.html.length > lastMsgCount && lastMsgCount !== 0) {
                    if (res.html.includes('message-received') && res.html.lastIndexOf('message-received') > res.html.lastIndexOf('message-sent')) {
                        // Jouer son si disponible
                    }
                }
                lastMsgCount = res.html.length;
                area.html(res.html);
                
                if(window.shouldScroll || isAtBottom) {
                    area.scrollTop(area[0].scrollHeight);
                    window.shouldScroll = false;
                }
            }
        }, 'json');
    }

    // --- TYPING STATUS & ONLINE CHECK ---
    function checkUserStatus() {
        if(!currentChatId) return;
        $.post('ajax_chat.php', { action: 'get_user_status', other_user_id: currentChatId }, function(res) {
            if(res.status === 'success') {
                $('#chatHeaderStatus').html(res.text);
            }
        }, 'json');
    }
    setInterval(checkUserStatus, 3000);

    // Typing Indicator Logic
    let typingTimer;
    $('#msgInput').on('input', function() {
        clearTimeout(typingTimer);
        updateTypingStatus(currentChatId); 
        typingTimer = setTimeout(function(){ updateTypingStatus(0); }, 1000);
    });

    function updateTypingStatus(targetId) {
        if (!currentChatId && targetId !== 0) return; 
        let finalTarget = (targetId === 0) ? 0 : currentChatId;
        $.post('ajax_chat.php', { action: 'set_typing_status', target_id: finalTarget });
    }

    // --- ENVOI MESSAGE ---
    $('#sendMessageForm').on('submit', function(e) {
        e.preventDefault();
        let msg = $('#msgInput').val().trim();
        let img = $('#imageInput')[0].files[0];
        
        if(msg == "" && !img) return;

        let formData = new FormData(this);
        let btn = $(this).find('button');
        btn.prop('disabled', true);

        $.ajax({
            url: 'ajax_chat.php',
            type: 'POST',
            data: formData,
            success: function (res) {
                btn.prop('disabled', false);
                if(res.status === 'success') {
                    $('#msgInput').val('');
                    removeImage();
                    window.shouldScroll = true;
                    loadMessages();
                    loadConversations();
                } else {
                    console.error("Error sending message: " + res.message);
                }
            },
            error: function() { btn.prop('disabled', false); },
            cache: false, contentType: false, processData: false, dataType: 'json'
        });
    });

    // --- UTILS ---
    window.startNewChat = function(id, name, avatar) {
        $('#searchResults').hide();
        $('#userSearchInput').val('');
        openChat(id, name, avatar);
    }

    window.closeChatMobile = function() {
        $('#chatWrapper').removeClass('chat-active');
        currentChatId = null;
    }

    $('#msgInput').on('keypress', function(e) {
        if (e.which == 13 && !e.shiftKey) { e.preventDefault(); $('#sendMessageForm').submit(); }
    });

    // --- SEARCH ---
    $('#userSearchInput').on('keyup', function() {
        let term = $(this).val();
        if(term.length < 2) { $('#searchResults').hide(); return; }
        
        $.post('ajax_chat.php', { action: 'search_users', term: term }, function(users) {
            let html = '';
            if(users && users.length > 0){
                users.forEach(u => {
                    let avatar = (u.avatar && u.avatar !== '') ? u.avatar : 'assets/img/avatar.png';
                    html += `<div class="search-result-item d-flex align-items-center" onclick="startNewChat(${u.id}, '${u.username}', '${avatar}')">
                                <img src="${avatar}" width="30" height="30" class="rounded-circle me-2"> 
                                <span>${u.username}</span>
                             </div>`;
                });
                $('#searchResults').html(html).show();
            } else { $('#searchResults').hide(); }
        }, 'json');
    });

    // --- EMOJI PICKER (v3.1.1) ---
    if (typeof EmojiButton !== 'undefined') {
        const button = document.querySelector('#emojiBtn');
        const picker = new EmojiButton({ position: 'top-start', theme: 'auto', showPreview: false, autoHide: false });

        picker.on('emoji', selection => {
            const input = document.querySelector('#msgInput');
            const emojiChar = (typeof selection === 'object' && selection.emoji) ? selection.emoji : selection;
            const start = input.selectionStart;
            const end = input.selectionEnd;
            input.value = input.value.substring(0, start) + emojiChar + input.value.substring(end);
            input.focus();
            input.selectionStart = input.selectionEnd = start + emojiChar.length;
        });

        button.addEventListener('click', () => { picker.togglePicker(button); });
    }

    // --- IMAGE PREVIEW ---
    $('#imageInput').on('change', function() {
        if(this.files && this.files[0]) showPreview(this.files[0]);
    });

    function showPreview(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#imgPreviewTag').attr('src', e.target.result);
            $('#imgPreviewArea').slideDown();
            $('label[for="imageInput"] i').removeClass('text-secondary').addClass('text-success');
        }
        reader.readAsDataURL(file);
    }

    window.removeImage = function() {
        $('#imageInput').val('');
        $('#imgPreviewArea').slideUp();
        $('label[for="imageInput"] i').removeClass('text-success').addClass('text-secondary');
    };

    // --- DRAG & DROP ---
    var dragTimer;
    var dropZone = $('.chat-main');

    dropZone.on('dragover', function(e) {
        e.preventDefault(); e.stopPropagation();
        if($('#inputArea').is(':visible')) { $('.chat-main').addClass('drag-active'); clearTimeout(dragTimer); }
    });

    dropZone.on('dragleave', function(e) {
        e.preventDefault(); e.stopPropagation();
        dragTimer = setTimeout(function() { $('.chat-main').removeClass('drag-active'); }, 100);
    });

    dropZone.on('drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $('.chat-main').removeClass('drag-active');
        if($('#inputArea').is(':visible') && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files.length > 0) {
            var file = e.originalEvent.dataTransfer.files[0];
            if(file.type.startsWith('image/')) {
                let container = new DataTransfer();
                container.items.add(file);
                $('#imageInput')[0].files = container.files;
                showPreview(file);
            } else { alert("Only images allowed."); }
        }
    });

    // --- GESTION STATUTS ---

    // 1. Ouvrir le modal d'ajout
    window.openStatusModal = function() {
        $('#statusModal').modal('show');
    };
    
    // Modification du HTML de la sidebar pour ajouter le onClick
    // (Cherchez la div "Mon statut" dans le HTML plus haut et ajoutez onclick="openStatusModal()" sur la div .chat-item)
    
    // 2. Prévisualisation Image Statut
    $('#statusImageInput').on('change', function() {
        if(this.files && this.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#statusImagePreview').html(`<img src="${e.target.result}" style="max-height:140px; max-width:100%;">`);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // 3. Envoyer le statut
    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        let btn = $(this).find('button[type="submit"]');
        
        // Petit effet visuel
        btn.prop('disabled', true).text('Publication...');

        $.ajax({
            url: 'ajax_chat.php', type: 'POST', data: formData,
            success: function(res) {
                btn.prop('disabled', false).text('Publier');
                
                if(res.status === 'success') {
                    $('#statusModal').modal('hide');
                    $('#statusForm')[0].reset();
                    $('#statusImagePreview').html('<i class="fas fa-camera fa-2x mb-2"></i><br>Ajouter une photo');
                    loadStatuses(); // Recharger la liste
                } else {
                    // --- ICI : Affiche l'erreur si ça plante ---
                    alert("Erreur lors de la publication : " + (res.message || "Erreur inconnue"));
                    console.log(res); // Regardez la console (F12) pour plus de détails
                }
            },
            error: function(xhr, status, error) {
                btn.prop('disabled', false).text('Publier');
                alert("Erreur technique (Ajax) : " + error);
                console.log(xhr.responseText);
            },
            cache: false, contentType: false, processData: false, dataType: 'json'
        });
    });

    // 4. Charger les statuts
    function loadStatuses() {
        // On ne charge que si l'onglet est actif
        // if (!$('#status-tab').hasClass('active')) return; // Optionnel : désactiver pour charger en arrière-plan

        $.post('ajax_chat.php', { action: 'fetch_statuses' }, function(res) {
            let html = `
            <div class="chat-item cursor-pointer" onclick="openStatusModal()">
                <div class="position-relative">
                    <img src="<?php echo htmlspecialchars($rowu['avatar']); ?>" class="rounded-circle" style="width:45px; height:45px; object-fit:cover;">
                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 16px; height: 16px; font-size:10px;"><i class="fas fa-plus"></i></span>
                </div>
                <div class="chat-info ms-3">
                    <div class="chat-name">Mon statut</div>
                    <div class="chat-preview">Touchez pour ajouter</div>
                </div>
            </div>
            <div class="p-3 small text-muted fw-bold bg-light">RÉCENTES</div>`;
            
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(s => {
                    // let thumb = (s.type === 'image') ? s.content : s.avatar; // Si image, on montre l'image, sinon avatar
                    let thumb = (s.type === 'image') ? s.content : cleanAvatarPath(s.avatar); // Si image, on montre l'image, sinon avatar
                    let text = (s.type === 'image') ? '📷 Photo' : s.content;
                    if(s.caption) text = s.caption;
                    
                    // Bordure verte pour les statuts non vus (simulation)
                    let borderClass = 'border border-success border-2 p-1'; 
                    
                    html += `
                    <div class="chat-item">
                        <div class="${borderClass} rounded-circle" style="width: 50px; height: 50px;">
                            <img src="${thumb}" class="rounded-circle w-100 h-100" style="object-fit:cover;">
                        </div>
                        <div class="chat-info ms-3">
                            <div class="chat-name">${s.username}</div>
                            <div class="chat-preview">${s.time_ago} - ${text}</div>
                        </div>
                    </div>`;
                });
            } else {
                html += '<div class="p-4 text-center small text-muted">Aucune mise à jour récente.</div>';
            }
            $('#status-content').html(html);
        }, 'json');
    }

    // Charger au clic sur l'onglet
    $('#status-tab').on('shown.bs.tab', loadStatuses);
});

// --- GESTION ARCHIVES ---
    window.openArchivedModal = function() {
        $('#archivedModal').modal('show');
        $.post('ajax_chat.php', { action: 'fetch_archived_conversations' }, function(res) {
            let html = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(c => {
                    html += `
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-white">
                        <div class="d-flex align-items-center">
                            <img src="${c.avatar}" class="rounded-circle me-3" width="40" height="40">
                            <strong>${c.username}</strong>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="toggleArchive(${c.conv_id})"><i class="fas fa-box-open"></i> Désarchiver</button>
                    </div>`;
                });
            } else { html = '<div class="p-4 text-center text-muted">Aucune archive.</div>'; }
            $('#archivedListBody').html(html);
        }, 'json');
    }

    window.toggleArchive = function(convId) {
        if(!confirm("Archiver/Désarchiver cette discussion ?")) return;
        $.post('ajax_chat.php', { action: 'toggle_archive', conv_id: convId }, function(res) {
            loadConversations(); // Rafraîchir la liste principale
            $('#archivedModal').modal('hide'); // Fermer le modal si ouvert
            // Si on est dans le chat, on peut fermer ou notifier
        }, 'json');
    }

    // --- GESTION FAVORIS (STAR) ---
    window.openStarredModal = function() {
        $('#starredModal').modal('show');
        $.post('ajax_chat.php', { action: 'fetch_starred_messages' }, function(res) {
            let html = '';
            if(res.status === 'success' && res.data.length > 0) {
                res.data.forEach(m => {
                    let content = (m.type === 'image') ? '<i class="fas fa-image"></i> Image' : m.message;
                    html += `
                    <div class="p-3 mb-2 bg-white border rounded shadow-sm mx-2 mt-2">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>${m.username}</span>
                            <span>${m.created_at}</span>
                        </div>
                        <div>${content}</div>
                    </div>`;
                });
            } else { html = '<div class="p-4 text-center text-muted">Aucun message favori.</div>'; }
            $('#starredListBody').html(html);
        }, 'json');
    }

    // Double-clic sur un message pour le mettre en favori (Astuce UX)
    $(document).on('dblclick', '.message-bubble', function() {
        // Idéalement, il faut ajouter data-msg-id="${msg.id}" dans le HTML de ajax_chat.php
        alert("Fonctionnalité : Ajoutez data-id aux messages pour activer le favori au clic !");
    });

</script>
<?php footer(); ?>
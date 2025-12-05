<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="dashboard.php" class="brand-link">
        <i class="fas fa-toolbox brand-image img-circle elevation-3" style="opacity: .8; padding-left: 10px; padding-top: 10px;"></i>
        <span class="brand-text font-weight-light">
            F.A Blog <?php echo ($user['role'] == 'Admin') ? '<span class="badge badge-success"><i class="fas fa-user-shield"></i> Admin</span>' : '<span class="badge badge-primary"><i class="fas fa-user-edit"></i> Editor</span>'; ?>
        </span>
    </a>
    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="../<?php echo htmlspecialchars($user['avatar']); ?>" class="img-circle elevation-2" alt="User Image" style="width: 50px; height: 50px; object-fit: cover;">
            </div>
            <div class="info pl-2">
                <a href="../profile" target="_blank" class="d-block font-weight-bold" style="line-height: 1.2; font-size: 1.1em;"><?php echo htmlspecialchars($user['username']); ?></a>
                <span class="badge <?php echo ($user['role'] == 'Admin') ? 'badge-danger' : 'badge-success'; ?> mt-1">
                    <?php echo htmlspecialchars($user['role']); ?>
                </span>
            </div>
        </div>
            
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link <?php if ($current_page == 'dashboard.php') echo 'active'; ?>">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <?php if ($user['role'] == 'Editor'): ?>
                <li class="nav-item">
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="nav-link <?php if ($current_page == 'edit_user.php') echo 'active'; ?>">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>My Profile</p>
                    </a>
                </li>
                <?php endif; ?>

                <?php if ($user['role'] == "Admin"): ?>
                <?php
                // --- GROUPE APPARENCE ---
                $appearance_pages = ['menu_editor.php', 'edit_menu.php', 'add_menu.php', 'widgets.php', 'edit_widget.php', 'add_widget.php'];
                $is_appearance_open = in_array($current_page, $appearance_pages);
                ?>
                <li class="nav-item <?php if ($is_appearance_open) echo 'menu-is-opening menu-open'; ?>">
                    <a href="#" class="nav-link <?php if ($is_appearance_open) echo 'active'; ?>">
                        <i class="nav-icon fas fa-paint-brush"></i>
                        <p>
                            Appearance
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="menu_editor.php" class="nav-link <?php if (in_array($current_page, ['menu_editor.php', 'add_menu.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-bars"></i>
                                <p>
                                    Menu Editor
                                    <span class="badge badge-success right"><?php echo $menu_published_count; ?></span>
                                    
                                    <?php if ($menu_draft_count > 0): ?>
                                    <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $menu_draft_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="widgets.php" class="nav-link <?php if (in_array($current_page, ['widgets.php', 'add_widget.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-puzzle-piece"></i>
                                <p>
                                    Widgets
                                    <span class="badge badge-success right"><?php echo $widget_active_count; ?></span>
                                    
                                    <?php if ($widget_inactive_count > 0): ?>
                                    <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $widget_inactive_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php
                // --- GROUPE SITE ---
                $site_pages = ['system-information.php', 'footer-pages.php', 'edit_footer_page.php', 'settings.php', 'maintenance.php', 'rss_imports.php', 'add_rss_import.php', 'messages.php', 'read_message.php', 'users.php', 'edit_user.php', 'add_user.php', 'bans.php', 'edit_ban.php', 'add_ban.php', 'newsletter.php', 'chats.php', 'backup.php'];
                $is_site_open = in_array($current_page, $site_pages);
                
                // Badge total pour le groupe SITE (messages + maintenance)
                $total_site_alerts = $unread_messages_count + ($maintenance_status == 'On' ? 1 : 0);
                ?>
                <li class="nav-item <?php if ($is_site_open) echo 'menu-is-opening menu-open'; ?>">
                    <a href="#" class="nav-link <?php if ($is_site_open) echo 'active'; ?>">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            Site
                            <?php if ($total_site_alerts > 0): ?>
                                <span class="badge badge-danger right"><?php echo $total_site_alerts; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="system-information.php" class="nav-link <?php if ($current_page == 'system-information.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-server fa-fw me-2 text-primary"></i>
                                <p>System Information</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="stats.php" class="nav-link <?php if ($current_page == 'stats.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-chart-line fa-fw me-2 text-warning"></i>
                                <p>Analytics</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="rss_imports.php" class="nav-link <?php echo ($current_page == 'rss_imports.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-rss text-warning"></i>
                                <p>Import RSS
                                    <?php if($badge_rss_count > 0): ?>
                                        <span class="badge badge-info right"><?php echo $badge_rss_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="settings.php" class="nav-link <?php if ($current_page == 'settings.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-cogs text-success"></i>
                                <p>Site Settings</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="bans.php" class="nav-link <?php if ($current_page == 'bans.php' || $current_page == 'add_ban.php') echo 'active'; ?>">
                                <span class="nav-icon fa-stack" style="font-size: 0.7em;">
                                    <i class="fas fa-user fa-stack-1x"></i>
                                    <i class="fas fa-ban fa-stack-2x text-danger"></i>
                                </span>
                                <p>
                                    Bans / Security
                                    <?php if($badge_bans_count > 0): ?>
                                        <span class="badge badge-danger right"><?php echo $badge_bans_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>   
                                                    
                        </li> <li class="nav-item">
                            <a href="footer_pages.php" class="nav-link <?php if (in_array($current_page, ['footer_pages.php', 'edit_footer_page.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>
                                    Pages Footer
                                    <?php if($badge_footer_pages_inactive > 0): ?>
                                        <span class="badge badge-danger right"><?php echo $badge_footer_pages_inactive; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success right"><?php echo $badge_footer_pages_active; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>                            

                        <li class="nav-item">
                            <a href="maintenance.php" class="nav-link <?php echo ($current_page == 'maintenance.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tools"></i>
                                <p>
                                    Site Maintenance
                                    <?php if($maintenance_status == 'On'): ?>
                                        <span class="badge badge-danger right">On</span>
                                    <?php else: ?>
                                        <span class="badge badge-success right">Off</span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>                            

                        <li class="nav-item">
                            <a href="messages.php" class="nav-link <?php if (in_array($current_page, ['messages.php', 'read_message.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-envelope"></i>
                                <p>Messages
                                    <?php if ($unread_messages_count > 0): ?>
                                    <span class="badge badge-danger right"><?php echo $unread_messages_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="users.php" class="nav-link <?php if (in_array($current_page, ['users.php', 'add_user.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Users <span class="badge badge-info right"><?php echo $total_users_count; ?></span></p>
                            </a>
                        </li>
                   
                        <li class="nav-item">
                            <a href="newsletter.php" class="nav-link <?php if ($current_page == 'newsletter.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-at"></i>
                                <p>Newsletter <span class="badge badge-info right"><?php echo $total_subscribers_count; ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="chats.php" class="nav-link <?php if(basename($_SERVER['PHP_SELF']) == 'chats.php' || basename($_SERVER['PHP_SELF']) == 'view_chat.php'){ echo 'active'; } ?>">
                                <i class="nav-icon fas fa-comments"></i>
                                <p>Chats</p>
                            </a>
                        </li>                            
                        <li class="nav-item">
                            <a href="backup.php" class="nav-link <?php if ($current_page == 'backup.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-database"></i>
                                <p>
                                    Database Backup
                                    <?php if ($badge_backup_count > 0): ?>
                                        <span class="badge badge-info right"><?php echo $badge_backup_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                
                <li class="nav-header">CONTENT</li>
                
                <?php
                // --- GROUPE POSTS ---
                $posts_pages = ['posts.php', 'edit_post.php', 'add_post.php', 'categorys.php', 'edit_category.php', 'add_category.php', 'comments.php', 'edit_comment.php'];
                $is_posts_open = in_array($current_page, $posts_pages);
                
                // Badge total pour le groupe BLOG (articles en attente + commentaires en attente)
                $total_blog_pending = $posts_pending_count + $pending_comments_count;
                ?>
                <!--- MENU PROJECTS -->
                <li class="nav-item has-treeview <?php if (in_array($current_page, ['projects.php', 'add_project.php', 'edit_project.php'])) echo 'menu-open'; ?>">
                    <a href="#" class="nav-link <?php if (in_array($current_page, ['projects.php', 'add_project.php', 'edit_project.php'])) echo 'active'; ?>">
                        <i class="nav-icon fas fa-microchip"></i>
                        <p>
                            Projects
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="projects.php" class="nav-link <?php if ($current_page == 'projects.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-list-alt"></i>
                                <p>All Projects</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_project.php" class="nav-link <?php if ($current_page == 'add_project.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Create Project</p>
                            </a>
                        </li>
                    </ul>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="projects.php" class="nav-link ...">
                                <i class="nav-icon fas fa-list"></i> <p>All Projects</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="project_categories.php" class="nav-link <?php if ($current_page == 'project_categories.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-list-alt"></i> <p>Categories</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_project.php" ...>...</a>
                        </li>
                    </ul>                
                </li>
                <!--- FIN MENU PROJECTS -->
                
                <li class="nav-item <?php if ($is_posts_open) echo 'menu-is-opening menu-open'; ?>">
                    <a href="#" class="nav-link <?php if ($is_posts_open) echo 'active'; ?>">
                        <i class="nav-icon fas fa-pen-square"></i>
                        <p>
                            Blog
                            <?php if ($total_blog_pending > 0 && $user['role'] == "Admin"): ?>
                                <span class="badge badge-warning right"><?php echo $total_blog_pending; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="add_post.php" class="nav-link <?php if ($current_page == 'add_post.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>Add Post</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="posts.php" class="nav-link <?php if ($current_page == 'posts.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-list"></i>
                                <p>
                                    All Posts
                                    
                                    <?php 
                                    $margin_right = 0;
                                    if ($posts_pending_count > 0): ?>
                                        <span class="badge badge-warning right" title="Pending"><?php echo $posts_pending_count; ?></span>
                                    <?php 
                                        $margin_right += 2.2;
                                    endif; ?>
                                    
                                    <?php if ($posts_scheduled_count > 0): ?>
                                        <span class="badge badge-info right" <?php if($margin_right > 0) echo 'style="margin-right: '.$margin_right.'rem;"'; ?> title="Scheduled"><?php echo $posts_scheduled_count; ?></span>
                                    <?php 
                                        $margin_right += 2.2;
                                    endif; ?>
                                    
                                    <?php if ($posts_draft_count > 0): ?>
                                        <span class="badge badge-secondary right" <?php if($margin_right > 0) echo 'style="margin-right: '.$margin_right.'rem;"'; ?> title="Drafts"><?php echo $posts_draft_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="categorys.php" class="nav-link <?php if (in_array($current_page, ['categorys.php', 'add_category.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-list-alt"></i>
                                <p>Categorys <span class="badge badge-info right"><?php echo $total_categories_count; ?></span></p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="comments.php" class="nav-link <?php if ($current_page == 'comments.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-comments"></i>
                                <p>Comments
                                    <?php if ($pending_comments_count > 0): ?>
                                    <span class="badge badge-warning right"><?php echo $pending_comments_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="tags.php" class="nav-link <?php if (in_array($current_page, ['tags.php', 'add_tag.php', 'edit_tag.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Tags <span class="badge badge-info right"><?php echo $count_tags; ?></span></p>
                            </a>
                        </li>
                    </ul>
                </li>
                 <?php if ($user['role'] == "Admin"): ?>
                <li class="nav-item <?php echo (in_array($current_page, ['popups.php', 'add_popup.php', 'edit_popup.php'])) ? 'menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['popups.php', 'add_popup.php', 'edit_popup.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-window-maximize"></i>
                        <p>
                            Popups
                            <?php if($badge_popups_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_popups_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="popups.php" class="nav-link <?php echo ($current_page == 'popups.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-window-maximize"></i>
                                <p>All Popups
                                    <span class="badge badge-success right"><?php echo $badge_popups_active; ?></span>
                                    <?php if ($badge_popups_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_popups_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_popup.php" class="nav-link <?php echo ($current_page == 'add_popup.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Add Popup</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (in_array($current_page, ['ads.php', 'add_ads.php', 'edit_ads.php', 'ads_stats.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['ads.php', 'add_ads.php', 'edit_ads.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-ad"></i> 
                        <p>
                            Manage Ads
                            <?php if($badge_ads_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_ads_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="ads.php" class="nav-link <?php echo ($current_page == 'ads.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list-ul"></i>
                                <p>
                                    List Ads
                                    <span class="badge badge-success right"><?php echo $badge_ads_active; ?></span>
                                    <?php if ($badge_ads_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_ads_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_ads.php" class="nav-link <?php echo ($current_page == 'add_ads.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Add New Ad</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="ads_stats.php" class="nav-link <?php echo ($current_page == 'ads_stats.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>Ads Statistics</p>
                            </a>
                        </li>                            
                    </ul>
                </li>
                <li class="nav-item <?php echo (in_array($current_page, ['mega_menus.php', 'add_mega_menu.php', 'edit_mega_menu.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['mega_menus.php', 'add_mega_menu.php', 'edit_mega_menu.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-columns"></i> <p>
                            Mega Menus
                            <?php if($badge_mega_menus_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_mega_menus_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="mega_menus.php" class="nav-link <?php echo ($current_page == 'mega_menus.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list-ul"></i>
                                <p>
                                    List All Menus
                                    <span class="badge badge-success right"><?php echo $badge_mega_menus_active; ?></span>
                                    <?php if ($badge_mega_menus_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_mega_menus_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_mega_menu.php" class="nav-link <?php echo ($current_page == 'add_mega_menu.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus-square"></i>
                                <p>Create New Menu</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
                <?php if ($user['role'] == "Admin"): ?>
                <?php
                // --- GROUPE PAGES ---
                $pages_pages = ['add_page.php', 'pages.php'];
                $is_pages_open = in_array($current_page, $pages_pages);
                ?>
                <li class="nav-item <?php if ($is_pages_open) echo 'menu-is-opening menu-open'; ?>">
                    <a href="#" class="nav-link <?php if ($is_pages_open) echo 'active'; ?>">
                        <i class="nav-icon fas fa-file-alt"></i>
                        <p>
                            Pages
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="add_page.php" class="nav-link <?php if ($current_page == 'add_page.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>Add Page</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="pages.php" class="nav-link <?php if (in_array($current_page, ['pages.php', 'add_page.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <p>
                                    All Pages
                                    <span class="badge badge-success right"><?php echo $pages_published_count; ?></span>
                                    
                                    <?php if ($pages_draft_count > 0): ?>
                                    <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $pages_draft_count; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php
                // --- GROUPE GALLERY ---
                $gallery_pages = ['add_image.php', 'gallery.php', 'edit_gallery.php', 'albums.php', 'edit_album.php', 'add_album.php'];
                $is_gallery_open = in_array($current_page, $gallery_pages);
                ?>
                <li class="nav-item <?php if ($is_gallery_open) echo 'menu-is-opening menu-open'; ?>">
                    <a href="#" class="nav-link <?php if ($is_gallery_open) echo 'active'; ?>">
                        <i class="nav-icon fas fa-images"></i>
                        <p>
                            Gallery
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="add_image.php" class="nav-link <?php if ($current_page == 'add_image.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-camera-retro"></i>
                                <p>Add Image</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="gallery.php" class="nav-link <?php if ($current_page == 'gallery.php') echo 'active'; ?>">
                                <i class="nav-icon fas fa-images"></i>
                                <p>All Images <span class="badge badge-info right"><?php echo $total_images_count; ?></span></p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="albums.php" class="nav-link <?php if (in_array($current_page, ['albums.php', 'add_album.php'])) echo 'active'; ?>">
                                <i class="nav-icon fas fa-list-ol"></i>
                                <p>Albums <span class="badge badge-info right"><?php echo $total_albums_count; ?></span></p>
                            </a>
                        </li>
                        
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="files.php" class="nav-link <?php if (in_array($current_page, ['files.php', 'upload_file.php'])) echo 'active'; ?>">
                        <i class="nav-icon fas fa-folder-open"></i>
                        <p>
                            Files
                            <span class="badge badge-info right"><?php echo $total_files_count; ?></span>
                        </p>
                    </a>
                </li>
                <?php if ($user['role'] == "Admin"): ?>
                <li class="nav-item <?php echo (in_array($current_page, ['polls.php', 'add_poll.php', 'edit_poll.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['polls.php', 'add_poll.php', 'edit_poll.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-poll"></i> <p>
                            Polls
                            <?php if($badge_polls_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_polls_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="polls.php" class="nav-link <?php echo ($current_page == 'polls.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list-ol"></i>
                                <p>List Polls
                                    <span class="badge badge-success right"><?php echo $badge_polls_active; ?></span>
                                    <?php if ($badge_polls_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_polls_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_poll.php" class="nav-link <?php echo ($current_page == 'add_poll.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <p>Create Poll</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (in_array($current_page, ['testimonials.php', 'add_testimonial.php', 'edit_testimonial.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['testimonials.php', 'add_testimonial.php', 'edit_testimonial.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-quote-left"></i>
                        <p>
                            Testimonials
                            <?php if($count_testi_pending > 0): ?>
                                <span class="badge badge-warning right"><?php echo $count_testi_pending; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="testimonials.php" class="nav-link <?php echo ($current_page == 'testimonials.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list"></i>
                                <p>List All 
                                    <?php if($count_testi_pending > 0): ?>
                                        <span class="badge badge-warning right"><?php echo $count_testi_pending; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_testimonial.php" class="nav-link <?php echo ($current_page == 'add_testimonial.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Add New</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (in_array($current_page, ['faq.php', 'add_faq.php', 'edit_faq.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['faq.php', 'add_faq.php', 'edit_faq.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-question-circle"></i>
                        <p>
                            FAQ Manager
                            <?php if($badge_faq_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_faq_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="faq.php" class="nav-link <?php echo ($current_page == 'faq.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list-ul"></i>
                                <p>List Questions
                                    <span class="badge badge-success right"><?php echo $badge_faq_active; ?></span>
                                    <?php if ($badge_faq_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_faq_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                                </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_faq.php" class="nav-link <?php echo ($current_page == 'add_faq.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Add Question</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item <?php echo (in_array($current_page, ['quizzes.php', 'add_quiz.php', 'edit_quiz.php', 'quiz_questions.php', 'add_question.php', 'edit_question.php', 'quiz_stats.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['quizzes.php', 'add_quiz.php', 'edit_quiz.php', 'quiz_questions.php', 'add_question.php', 'edit_question.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-graduation-cap"></i>
                        <p>
                            Quiz Manager
                            <?php if($badge_quiz_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_quiz_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="quizzes.php" class="nav-link <?php echo (in_array($current_page, ['quizzes.php', 'add_quiz.php', 'edit_quiz.php'])) ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list-ul"></i>
                                <p>Manage Quizzes
                                    <span class="badge badge-success right"><?php echo $badge_quiz_active; ?></span>
                                    <?php if ($badge_quiz_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_quiz_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                                </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_quiz.php" class="nav-link <?php echo ($current_page == 'add_quiz.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Add New Quiz</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="quiz_stats.php" class="nav-link <?php if ($current_page == 'quiz_stats.php') echo 'active'; ?>">
                                <i class="far fa-chart-bar nav-icon"></i> <p>Quiz Statistics</p>
                            </a>
                        </li>                           
                    </ul>
                </li>
                <li class="nav-item <?php echo (in_array($current_page, ['slides.php', 'add_slide.php', 'edit_slide.php'])) ? 'menu-is-opening menu-open' : ''; ?>">
                    <a href="#" class="nav-link <?php echo (in_array($current_page, ['slides.php', 'add_slide.php', 'edit_slide.php'])) ? 'active' : ''; ?>">
                        <i class="nav-icon fas fa-images"></i>
                        <p>
                            Manage Slider
                            <?php if($badge_slides_inactive > 0): ?>
                                <span class="badge badge-warning right"><?php echo $badge_slides_inactive; ?></span>
                            <?php else: ?>
                                <i class="right fas fa-angle-left"></i>
                            <?php endif; ?>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="slides.php" class="nav-link <?php echo ($current_page == 'slides.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-list"></i>
                                <p>List Slides
                                    <span class="badge badge-success right"><?php echo $badge_slides_active; ?></span>
                                    <?php if ($badge_slides_inactive > 0): ?>
                                        <span class="badge badge-warning right" style="margin-right: 2.2rem;"><?php echo $badge_slides_inactive; ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="add_slide.php" class="nav-link <?php echo ($current_page == 'add_slide.php') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-plus"></i>
                                <p>Add New Slide</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </nav> 
        </div> 
</aside>
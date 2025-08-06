<?php
/**
 * Dashboard Header Component
 */
$current_user = wp_get_current_user();
$dashboard_url = current_user_can('realtor') ? home_url('/realtor-dashboard') : home_url('/');
?>

<header class="dashboard-header">
    <a href="<?php echo esc_url($dashboard_url); ?>">
        <img src="<?php echo esc_url(content_url('/uploads/2025/08/mary-logo.png')); ?>" 
             alt="<?php echo esc_attr(get_bloginfo('name')); ?> Logo" 
             class="site-logo">
    </a>

    <div class="user-info">
        <a href="?tab=notifications">
            <div class="notification-icon">
                <span class="dashicons dashicons-bell"></span>
            </div>
        </a>

        <div class="profile-header">
            <div class="profile-pic">
                <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="Profile Picture">
            </div>
        </div>

        <div class="user-details">
            <span class="user-name"><?php echo esc_html($current_user->display_name); ?></span>
            <span class="user-role">
                <?php
                $role_names = [
                    'realtor' => 'Realtor',
                    'agent' => 'Agent',
                    'subscriber' => 'Subscriber'
                ];
                $user_roles = $current_user->roles;
                echo esc_html($role_names[$user_roles[0]] ?? ucfirst($user_roles[0]));
                ?>
            </span>
        </div>
    </div>
</header>
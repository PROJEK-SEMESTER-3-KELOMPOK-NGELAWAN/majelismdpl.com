<?php
class RoleHelper {
    const USER = 'user';
    const ADMIN = 'admin';
    const SUPER_ADMIN = 'super_admin';
    
    /**
     * Check role hierarchy - apakah user memiliki permission minimal tertentu
     */
    public static function hasPermission($required_role, $user_role) {
        $hierarchy = [
            self::USER => 1,
            self::ADMIN => 2,
            self::SUPER_ADMIN => 3
        ];
        
        return isset($hierarchy[$user_role]) && isset($hierarchy[$required_role]) 
               && $hierarchy[$user_role] >= $hierarchy[$required_role];
    }
    
    /**
     * Check apakah user adalah super admin
     */
    public static function isSuperAdmin($role) {
        return $role === self::SUPER_ADMIN;
    }
    
    /**
     * Check apakah user adalah admin (regular admin atau super admin)
     */
    public static function isAdmin($role) {
        return in_array($role, [self::ADMIN, self::SUPER_ADMIN]);
    }
    
    /**
     * Check apakah user hanya admin biasa (bukan super admin)
     */
    public static function isRegularAdmin($role) {
        return $role === self::ADMIN;
    }
    
    /**
     * Check apakah user bisa akses Master Admin (hanya super admin)
     */
    public static function canAccessMasterAdmin($role) {
        return self::isSuperAdmin($role);
    }
    
    /**
     * Check apakah user bisa manage trips
     */
    public static function canManageTrips($role) {
        return self::hasPermission(self::ADMIN, $role);
    }
    
    /**
     * Check apakah user bisa manage peserta
     */
    public static function canManageParticipants($role) {
        return self::hasPermission(self::ADMIN, $role);
    }
    
    /**
     * Check apakah user bisa manage pembayaran
     */
    public static function canManagePayments($role) {
        return self::hasPermission(self::ADMIN, $role);
    }
    
    /**
     * Check apakah user bisa manage galeri
     */
    public static function canManageGallery($role) {
        return self::hasPermission(self::ADMIN, $role);
    }
    
    /**
     * Get role display name
     */
    public static function getRoleDisplayName($role) {
        $names = [
            self::USER => 'User',
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin'
        ];
        
        return $names[$role] ?? 'Unknown';
    }
    
    /**
     * Get role badge class untuk styling
     */
    public static function getRoleBadgeClass($role) {
        $classes = [
            self::USER => 'bg-secondary',
            self::ADMIN => 'bg-brown',
            self::SUPER_ADMIN => 'bg-danger'
        ];
        
        return $classes[$role] ?? 'bg-secondary';
    }
    
    /**
     * Get all available roles
     */
    public static function getAllRoles() {
        return [
            self::USER => 'User',
            self::ADMIN => 'Admin', 
            self::SUPER_ADMIN => 'Super Admin'
        ];
    }
    
    /**
     * Get admin roles only (untuk dropdown di form)
     */
    public static function getAdminRoles() {
        return [
            self::ADMIN => 'Admin',
            self::SUPER_ADMIN => 'Super Admin'
        ];
    }
}
?>

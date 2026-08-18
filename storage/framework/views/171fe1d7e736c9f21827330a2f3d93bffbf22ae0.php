

<?php $__env->startSection('title', 'Role Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Role Management</h1>
        <a href="<?php echo e(route('roles.create')); ?>" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg">
            <i class="fas fa-plus mr-2"></i> Create Role
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Menus</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <strong><?php echo e($role->name); ?></strong>
                        <?php if($role->is_system): ?>
                            <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded">System</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?php echo e($role->description); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                            <?php echo e($role->users_count); ?> user<?php echo e($role->users_count != 1 ? 's' : ''); ?>

                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">
                        <?php
                            $menus = array_keys(array_filter($role->menu_access, fn($v) => $v === true));
                        ?>
                        <span class="text-gray-600"><?php echo e(count($menus)); ?>/10 menus</span>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap text-sm">
                        <?php if(!$role->is_system): ?>
                            <a href="<?php echo e(route('roles.edit', $role)); ?>" class="text-blue-600 hover:text-blue-900 mr-4">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <!--<a href="<?php echo e(route('roles.clone', $role)); ?>" class="text-green-600 hover:text-green-900 mr-4" onclick="return confirm('Clone this role?')">-->
                            <!--    <i class="fas fa-copy"></i> Clone-->
                            <!--</a>-->
                            <form method="POST" action="<?php echo e(route('roles.destroy', $role)); ?>" style="display:inline;" onsubmit="return confirm('Delete this role?')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-gray-400">No actions</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH F:\xampp\htdocs\sardancoCrm\resources\views/roles/index.blade.php ENDPATH**/ ?>
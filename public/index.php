 <?php
    require_once __DIR__ . '/../includes/header.php';
    require_once '../controllers/auth.php';
    ?>

 <div class="min-h-screen bg-slate-100">
     <!-- Hero Section -->
     <div class="bg-slate-800 text-white py-16">
         <div class="container mx-auto px-4">
             <h1 class="text-4xl font-bold mb-4">Laboratory Stock Management System</h1>
             <p class="text-xl text-slate-300">Efficiently manage your laboratory inventory with our comprehensive system</p>
         </div>
     </div>

     <div class="container mx-auto px-4 py-8">
         <div class="grid md:grid-cols-2 gap-8">
             <!-- Features Section -->
             <div class="space-y-6">
                 <h2 class="text-2xl font-bold text-slate-800 mb-4">Key Features</h2>

                 <div class="grid gap-4">
                     <div class="p-4 bg-white rounded-lg shadow-md border border-slate-200">
                         <h3 class="font-semibold text-slate-700">Inventory Tracking</h3>
                         <p class="text-slate-600">Monitor stock levels and manage item transactions efficiently</p>
                     </div>

                     <div class="p-4 bg-white rounded-lg shadow-md border border-slate-200">
                         <h3 class="font-semibold text-slate-700">Expiry Management</h3>
                         <p class="text-slate-600">Track and manage expiry dates of laboratory items</p>
                     </div>

                     <div class="p-4 bg-white rounded-lg shadow-md border border-slate-200">
                         <h3 class="font-semibold text-slate-700">Order Management</h3>
                         <p class="text-slate-600">Place and track orders from suppliers</p>
                     </div>

                     <div class="p-4 bg-white rounded-lg shadow-md border border-slate-200">
                         <h3 class="font-semibold text-slate-700">Alert System</h3>
                         <p class="text-slate-600">Get notified about low stock and expired items</p>
                     </div>
                 </div>
             </div>

             <!-- Login Form -->
             <div class="bg-white p-8 rounded-lg shadow-md border border-slate-200">
                 <h2 class="text-2xl font-bold text-slate-800 mb-6">Login</h2>

                 <?php if (isset($_SESSION['error'])): ?>
                     <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                         <?php echo $_SESSION['error'];
                            unset($_SESSION['error']); ?>
                     </div>
                 <?php endif; ?>

                 <form action="" method="POST" class="space-y-4">
                     <div>
                         <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                         <input type="email" name="email" id="email" required
                             class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                     </div>

                     <div>
                         <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                         <input type="password" name="password" id="password" required
                             class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                     </div>

                     <button type="submit"
                         class="w-full bg-slate-800 text-white rounded-md px-4 py-2 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                         Sign In
                     </button>
                 </form>

                 <div class="mt-4 text-sm text-slate-600">
                     <p class="font-semibold">Demo Credentials:</p>
                     <ul class="list-disc list-inside space-y-1 mt-2">
                         <li>Admin: admin@lab.com / admin123</li>
                         <li>Manager: manager@lab.com / manager123</li>
                         <li>Viewer: viewer@lab.com / viewer123</li>
                     </ul>
                 </div>
             </div>
         </div>
     </div>
 </div>

 <?php require_once __DIR__ . '/../includes/footer.php'; ?>
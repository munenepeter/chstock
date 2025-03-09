 <?php
    if (!session_name() || session_id() == '') {
        session_start();
    }

    require_once __DIR__ . '/../autoload.php';
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>LAB STOCK</title>
     <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,container-queries"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     <script>
         function toggleMobileSubmenu(submenuId) {
             const submenu = document.getElementById(submenuId);
             if (submenu) {
                 submenu.classList.toggle('hidden');
             }
         }
     </script>
     <style type="text/tailwindcss">
         .input {
             @apply mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 shadow-sm focus:ring focus:ring-slate-300;
         }

         .btn-primary {
             @apply bg-slate-700 text-white px-4 py-2 rounded-md hover:bg-slate-800 transition;
         }

         .btn-secondary {
             @apply text-slate-600 hover:text-slate-800 px-4 py-2 rounded-md transition;
         }
     </style>
 </head>

 <body class="min-h-screen bg-slate-50">
     <nav class="bg-slate-800 border-b border-slate-700">
         <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
             <div class="flex h-16 items-center justify-between">
                 <!-- Logo and primary nav -->
                 <div class="flex items-center">
                     <div class="flex-shrink-0">
                         <a href="/dashboard.php" class="text-white font-bold text-xl">LAB STOCK</a>
                     </div>

                     <?php if (isset($_SESSION['user'])): ?>
                         <div class="hidden md:block ml-10">
                             <div class="flex items-center space-x-4">
                                 <!-- Main Navigation -->
                                 <div class="relative group">
                                     <a href="/dashboard.php"
                                         class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                         Dashboard
                                     </a>
                                 </div>

                                 <?php if (in_array($_SESSION['user']['role'], ['admin', 'store_manager'])): ?>
                                     <!-- Acquisition Dropdown -->
                                     <div class="relative group">
                                         <button class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                             <span>Acquisition</span>
                                             <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                             </svg>
                                         </button>
                                         <div class="absolute left-0 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out z-50">
                                             <div class="py-1" role="menu" aria-orientation="vertical">
                                                 <a href="/acquisition/requisitions/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Requisition Orders</a>
                                                 <a href="/items/view.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Items</a>
                                                 <a href="/departments/view.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Departments</a>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Quotation Dropdown -->
                                     <div class="relative group">
                                         <button class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                             <span>Quotation</span>
                                             <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                             </svg>
                                         </button>
                                         <div class="absolute left-0 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out z-50">
                                             <div class="py-1" role="menu" aria-orientation="vertical">
                                                 <a href="/quotation/requests/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Quotation Requests</a>
                                                 <a href="/suppliers/view.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Suppliers</a>
                                             </div>
                                         </div>
                                     </div>

                                     <!-- Procurement Dropdown -->
                                     <div class="relative group">
                                         <button class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                             <span>Procurement</span>
                                             <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                             </svg>
                                         </button>
                                         <div class="absolute left-0 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out z-50">
                                             <div class="py-1" role="menu" aria-orientation="vertical">
                                                 <a href="/purchase_orders/view.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Purchase Orders</a>
                                                 <a href="/procurement/contracts/" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Contracts</a>
                                             </div>
                                         </div>
                                     </div>
                                 <?php endif; ?>

                                 <!-- Stock Management -->
                                 <div class="relative group">
                                     <button class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium inline-flex items-center">
                                         <span>Stock</span>
                                         <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                         </svg>
                                     </button>
                                     <div class="absolute left-0 mt-1 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 ease-in-out z-50">
                                         <div class="py-1" role="menu" aria-orientation="vertical">
                                             <a href="/stock/view.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">View Stock</a>
                                             <?php if (in_array($_SESSION['user']['role'], ['admin', 'store_manager'])): ?>
                                                 <a href="/stock/manage.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Manage Stock</a>
                                             <?php endif; ?>
                                         </div>
                                     </div>
                                 </div>

                                 <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                                     <!-- Reports -->
                                     <div class="relative group">
                                         <a href="/reports/view.php"
                                             class="text-slate-300 hover:bg-slate-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                                             Reports
                                         </a>
                                     </div>
                                 <?php endif; ?>
                             </div>
                         </div>
                     <?php endif; ?>
                 </div>

                 <!-- Secondary nav -->
                 <div class="hidden md:block">
                     <div class="flex items-center">
                         <?php if (isset($_SESSION['user'])): ?>
                             <span class="text-slate-300 mr-4">
                                 <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                             </span>
                             <a href="/logout.php"
                                 class="bg-slate-700 hover:bg-slate-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                 Logout
                             </a>
                         <?php endif; ?>
                     </div>
                 </div>

                 <!-- Mobile menu button -->
                 <?php if (isset($_SESSION['user'])): ?>
                     <div class="md:hidden">
                         <button type="button"
                             onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                             class="inline-flex items-center justify-center p-2 rounded-md text-slate-400 hover:text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-slate-800 focus:ring-white">
                             <span class="sr-only">Open main menu</span>
                             <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                             </svg>
                         </button>
                     </div>
                 <?php endif; ?>
             </div>

             <!-- Mobile menu -->
             <?php if (isset($_SESSION['user'])): ?>
                 <div class="md:hidden hidden" id="mobile-menu">
                     <div class="px-2 pt-2 pb-3 space-y-1">
                         <a href="/dashboard.php"
                             class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">
                             Dashboard
                         </a>

                         <?php if (in_array($_SESSION['user']['role'], ['admin', 'store_manager'])): ?>
                             <!-- Acquisition Section -->
                             <div class="space-y-1">
                                 <button onclick="toggleMobileSubmenu('acquisitionSubmenu')"
                                     class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium w-full text-left flex items-center justify-between">
                                     <span>Acquisition</span>
                                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                     </svg>
                                 </button>
                                 <div id="acquisitionSubmenu" class="hidden pl-4">
                                     <a href="/acquisition/requisitions/" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Requisition Orders</a>
                                     <a href="/items/view.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Items</a>
                                     <a href="/departments/view.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Departments</a>
                                 </div>
                             </div>

                             <!-- Quotation Section -->
                             <div class="space-y-1">
                                 <button onclick="toggleMobileSubmenu('quotationSubmenu')"
                                     class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium w-full text-left flex items-center justify-between">
                                     <span>Quotation</span>
                                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                     </svg>
                                 </button>
                                 <div id="quotationSubmenu" class="hidden pl-4">
                                     <a href="/quotation/requests/" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Quotation Requests</a>
                                     <a href="/suppliers/view.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Suppliers</a>
                                 </div>
                             </div>

                             <!-- Procurement Section -->
                             <div class="space-y-1">
                                 <button onclick="toggleMobileSubmenu('procurementSubmenu')"
                                     class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium w-full text-left flex items-center justify-between">
                                     <span>Procurement</span>
                                     <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                     </svg>
                                 </button>
                                 <div id="procurementSubmenu" class="hidden pl-4">
                                     <a href="/procurement/purchase-orders/" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Purchase Orders</a>
                                     <a href="/procurement/contracts/" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Contracts</a>
                                 </div>
                             </div>
                         <?php endif; ?>

                         <!-- Stock Section -->
                         <div class="space-y-1">
                             <button onclick="toggleMobileSubmenu('stockSubmenu')"
                                 class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium w-full text-left flex items-center justify-between">
                                 <span>Stock</span>
                                 <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                 </svg>
                             </button>
                             <div id="stockSubmenu" class="hidden pl-4">
                                 <a href="/stock/view.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">View Stock</a>
                                 <?php if (in_array($_SESSION['user']['role'], ['admin', 'store_manager'])): ?>
                                     <a href="/stock/manage.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-sm">Manage Stock</a>
                                 <?php endif; ?>
                             </div>
                         </div>

                         <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                             <a href="/reports/view.php"
                                 class="text-slate-300 hover:bg-slate-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">
                                 Reports
                             </a>
                         <?php endif; ?>
                     </div>
                 </div>
             <?php endif; ?>
         </div>
     </nav>

     <!-- Page Content -->
     <main class="flex-grow"><?php if (isset($_SESSION['success'])): ?>
             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                 <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                     <?php echo $_SESSION['success'];
                                    unset($_SESSION['success']); ?>
                 </div>
             </div>
         <?php endif; ?>
<?php
// components/cart.php
?>
<div id="cartOverlay" class="fixed inset-0 bg-black opacity-50 hidden z-40"></div>
<div id="cartSidebar"
     class="fixed top-0 right-0 h-full w-80 bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
  <div class="p-4 flex justify-between items-center border-b">
    <h2 class="text-xl font-semibold">Meu Carrinho</h2>
    <button id="closeCart" class="text-gray-600 hover:text-gray-800">✕</button>
  </div>
  <div id="cartItems" class="p-4 overflow-y-auto flex-1"></div>
  <div class="p-4 border-t">
    <div class="flex justify-between mb-4">
      <span class="font-bold">Total</span>
      <span id="cartTotal">R$ 0,00</span>
    </div>
    <button id="checkout"
            class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700">
      Finalizar Pedido
    </button>
  </div>
</div>

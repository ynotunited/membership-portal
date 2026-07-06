<?php
$title = 'GAFCONL Rice Project';
ob_start();
?>

<div class="max-w-4xl mx-auto">
    <!-- Header/Video Section -->
    <div class="relative bg-black rounded-2xl overflow-hidden mb-8 shadow-2xl group">
        <div class="aspect-w-16 aspect-h-9 relative h-[400px]">
            <!-- Placeholder for Video - Using a relevant image background for now -->
            <img src="<?= \App\Helpers\Url::appUrl() ?>/uploads/42264.jpg" alt="Rice Field"
                class="w-full h-full object-cover opacity-60">

            <div class="absolute inset-0 flex items-center justify-center">
                <button onclick="document.getElementById('videoModal').classList.remove('hidden')"
                    class="bg-white/20 backdrop-blur-sm p-4 rounded-full hover:bg-white/30 transition-all transform hover:scale-110 group-hover:animate-pulse">
                    <i class="ri-play-fill text-6xl text-white ml-2"></i>
                </button>
            </div>

            <div class="absolute bottom-0 left-0 right-0 p-8 bg-gradient-to-t from-black/80 to-transparent">
                <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">GAFCONL RICE PROJECT</h1>
                <p class="text-green-400 font-semibold text-lg">Building the Future of Agriculture</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Project Update Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                        <i class="ri-plant-line text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Project Status Update</h2>
                        <span class="text-sm text-green-600 font-medium">Recorded Significant Progress</span>
                    </div>
                </div>

                <div class="prose text-gray-600">
                    <p class="mb-4">We have recorded significant progress with our rice farming project. The milestones
                        so far achieved are:</p>

                    <ul class="space-y-4 mb-6">
                        <li class="flex items-start">
                            <i class="ri-checkbox-circle-fill text-green-500 mt-1 mr-3"></i>
                            <span><strong class="text-gray-900">Land acquisition:</strong> we leased the land for 10
                                years and payment has been made.</span>
                        </li>
                        <li class="flex items-start">
                            <i class="ri-checkbox-circle-fill text-green-500 mt-1 mr-3"></i>
                            <span><strong class="text-gray-900">Mapping and survey:</strong> we have obtained a drone
                                footage and carried out the mapping of the land.</span>
                        </li>
                    </ul>

                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
                        <h3 class="font-bold text-blue-900 text-sm uppercase mb-1">Upcoming Milestone</h3>
                        <p class="text-blue-800">We are moving on to clearing and preparation of the land for planting,
                            which is going to be completed on or before <strong class="text-blue-900">Monday, 26th of
                                January 2026</strong>.</p>
                    </div>
                </div>
            </div>

            <!-- Investment Info -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border border-green-100 p-6">
                <h3 class="text-lg font-bold text-green-900 mb-4 flex items-center">
                    <i class="ri-money-dollar-circle-line mr-2"></i> Investment Details
                </h3>
                <div class="bg-white rounded-lg p-5 shadow-sm mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Interest Rate</span>
                        <span class="text-2xl font-bold text-green-600">38% <span
                                class="text-sm font-normal text-gray-500">per annum</span></span>
                    </div>
                    <p class="text-sm text-gray-500">Secure your future with high-yield agricultural returns.</p>
                </div>

                <div class="space-y-3">
                    <h4 class="font-semibold text-green-900 mb-2">Bank Details for Payment:</h4>
                    <div class="bg-white p-4 rounded-lg border border-green-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Bank Name</p>
                                <p class="font-bold text-gray-900">First Bank Plc</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase">Account Number</p>
                                <div class="flex items-center">
                                    <p class="font-bold text-gray-900 text-xl mr-2">2045697533</p>
                                    <button onclick="navigator.clipboard.writeText('2045697533')"
                                        class="text-green-600 hover:text-green-800">
                                        <i class="ri-file-copy-line"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500 uppercase">Account Name</p>
                                <p class="font-bold text-gray-900">Global Apex Farmers Cooperative Nigeria Limited</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- My Investments History -->
            <div class="mt-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4">My Investment History</h3>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <?php if (empty($investments)): ?>
                        <div class="p-8 text-center text-gray-500">
                            <i class="ri-history-line text-4xl mb-2 text-gray-300"></i>
                            <p>You haven't made any investments yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-6 py-3 font-semibold text-gray-700">Date</th>
                                        <th class="px-6 py-3 font-semibold text-gray-700">Amount</th>
                                        <th class="px-6 py-3 font-semibold text-gray-700">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($investments as $inv): ?>
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="px-6 py-4 text-gray-600">
                                                <?= date('M d, Y', strtotime($inv['created_at'])) ?>
                                            </td>
                                            <td class="px-6 py-4 font-bold text-gray-900">
                                                ₦<?= number_format($inv['amount'], 2) ?>
                                            </td>
                                            <td class="px-6 py-4">
                                                <?php if ($inv['status'] === 'approved'): ?>
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                <?php elseif ($inv['status'] === 'rejected'): ?>
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                        Rejected
                                                    </span>
                                                <?php else: ?>
                                                    <span
                                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar / Action Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 sticky top-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Invest Now</h3>

                <form action="<?= \App\Helpers\Url::appUrl() ?>/rice-project/submit" method="POST"
                    enctype="multipart/form-data">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Investment Amount (₦)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-400">₦</span>
                                <input type="number" name="amount" required min="1000"
                                    class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Payment Receipt</label>
                            <input type="file" name="payment_receipt" required accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                            <p class="text-xs text-gray-500 mt-1">Accepts JPG, PNG, PDF</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                            <textarea name="notes" rows="2"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>

                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg shadow transition-colors flex items-center justify-center">
                            <i class="ri-secure-payment-line mr-2"></i> Submit Investment
                        </button>
                    </div>
                </form>

                <!-- Contact Info -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="font-semibold text-gray-900 mb-3 text-sm">For Enquiries Contact:</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center"><i class="ri-phone-line text-green-500 mr-2"></i> 0816 556 0000
                        </li>
                        <li class="flex items-center"><i class="ri-phone-line text-green-500 mr-2"></i> 0802 731 0755
                        </li>
                        <li class="flex items-center"><i class="ri-phone-line text-green-500 mr-2"></i> 0803 422 2542
                        </li>
                        <li class="flex items-center"><i class="ri-phone-line text-green-500 mr-2"></i> 0703 228 8225
                        </li>
                        <li class="flex items-center"><i class="ri-phone-line text-green-500 mr-2"></i> 0707 075 9999
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div id="videoModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
    aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="document.getElementById('videoModal').classList.add('hidden'); document.getElementById('projectVideo').pause();">
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div
            class="inline-block align-bottom bg-black rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="relative bg-black">
                <video id="projectVideo" controls class="w-full h-auto max-h-[80vh]">
                    <source src="<?= \App\Helpers\Url::appUrl() ?>/uploads/videos/rice_project.mp4" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <button
                    onclick="document.getElementById('videoModal').classList.add('hidden'); document.getElementById('projectVideo').pause();"
                    class="absolute top-4 right-4 text-white hover:text-red-500 z-10 bg-black/50 rounded-full p-2">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/user.php';
?>
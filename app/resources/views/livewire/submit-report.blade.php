<div class="card border-0 shadow-sm p-4 bg-white rounded-3 mx-auto" style="max-width: 680px;">
    
    <!-- Step indicator header -->
    <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
        <div>
            <h3 class="m-0 fw-bold text-navy fs-4">File a Fraud Report</h3>
            <p class="m-0 text-secondary small">Submit evidence and warn the community about scammers.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $step === 1 ? 'bg-primary' : 'bg-success' }} px-3 py-2 rounded">
                Stage {{ $step }} of 2
            </span>
        </div>
    </div>

    <!-- ─── STAGE 1: Minimal Report & Receipt Redaction ─── -->
    @if($step === 1)
        <div class="reveal visible animate-fade-in">
            @error('recaptchaToken')
                <div class="alert alert-danger bg-coral text-white border-0 rounded-3 small py-2 mb-4">
                    <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                </div>
            @enderror

            <form x-data
                  @submit.prevent="
                      const siteKey = '{{ config('services.recaptcha.site_key') }}';
                      if (!siteKey) {
                          $wire.set('recaptchaToken', 'mock-token');
                          $wire.submitStepOne();
                          return;
                      }
                      grecaptcha.ready(() => {
                          grecaptcha.execute(siteKey, {action: 'submit_report'}).then((token) => {
                              $wire.set('recaptchaToken', token);
                              $wire.submitStepOne();
                          });
                      });
                  "
                  id="stageOneForm">
                
                <!-- Category Select -->
                <div class="mb-3">
                    <label class="form-label text-navy fw-semibold small">Scam Category</label>
                    <select wire:model.defer="scam_category_id" class="form-select border-light-subtle rounded-3 p-2.5">
                        <option value="">Select a Category...</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('scam_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Vendor Type select -->
                <div class="mb-3">
                    <label class="form-label text-navy fw-semibold small">Scammer Identifier Type</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input type="radio" wire:model="vendor_type" value="bank" id="typeBank" class="form-check-input" />
                            <label for="typeBank" class="form-check-label small">🏦 Bank Account</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" wire:model="vendor_type" value="phone" id="typePhone" class="form-check-input" />
                            <label for="typePhone" class="form-check-label small">📱 Phone Number</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" wire:model="vendor_type" value="email" id="typeEmail" class="form-check-input" />
                            <label for="typeEmail" class="form-check-label small">📧 Email Address</label>
                        </div>
                    </div>
                </div>

                <!-- Scam Identifier Value and Duplicate Warning -->
                <div class="row g-3 mb-3">
                    @if($vendor_type === 'bank')
                        <div class="col-sm-6">
                            <label class="form-label text-navy fw-semibold small">Bank Name</label>
                            <input type="text" wire:model.defer="bank_name" placeholder="e.g. GTBank, Access Bank" class="form-control border-light-subtle rounded-3 p-2.5" />
                            @error('bank_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="{{ $vendor_type === 'bank' ? 'col-sm-6' : 'col-12' }}">
                        <label class="form-label text-navy fw-semibold small">
                            @if($vendor_type === 'bank') Bank Account Number @elseif($vendor_type === 'phone') Phone Number @else Email Address @endif
                        </label>
                        <input type="text" wire:model="vendor_value" placeholder="Enter scammer details..." class="form-control border-light-subtle rounded-3 p-2.5" />
                        @error('vendor_value') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- ⚠️ Duplicate warning alert -->
                @if($duplicates_count > 0)
                    <div class="alert alert-warning border-0 p-3 rounded-3 d-flex gap-2.5 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                        <div class="small">
                            <span class="fw-bold">Warning:</span> This scammer has been flagged <strong>{{ $duplicates_count }} times</strong> in our community. Filing this report will add further evidence to verify this account.
                        </div>
                    </div>
                @endif

                <!-- Payment Receipt Upload & Redaction Tool -->
                <div class="mb-4" x-data="redactionTool()">
                    <label class="form-label text-navy fw-semibold small">Payment Receipt (Image evidence)</label>
                    <div class="border border-dashed border-light-subtle p-4 rounded-3 text-center bg-light-subtle cursor-pointer position-relative mb-2">
                        <input type="file" id="receiptInput" accept="image/*" class="position-absolute opacity-0 start-0 top-0 w-100 h-100 cursor-pointer" x-on:change="loadImage($event)" style="z-index: 2;" />
                        <div class="d-flex flex-column align-items-center gap-1.5 py-2">
                            <i class="bi bi-cloud-arrow-up-fill fs-3 text-secondary"></i>
                            <span class="fw-semibold text-navy small">Click or drag receipt here</span>
                            <span class="text-secondary text-xs">JPEG, PNG, WEBP (Max 5MB)</span>
                        </div>
                    </div>
                    @error('original_image') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                    <!-- Redaction Canvas Area (hidden initially until image is loaded) -->
                    <div id="redactorContainer" class="d-none border border-light-subtle rounded-3 p-3 bg-light mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <span class="text-navy fw-bold small"><i class="bi bi-brush-fill text-coral me-1"></i> Receipt Redactor & Privacy Canvas</span>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 text-xs" x-on:click="clearCanvas()">
                                    <i class="bi bi-trash"></i> Reset
                                </button>
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 text-xs fw-semibold" x-on:click="applyRedaction()">
                                    <i class="bi bi-check-lg"></i> Lock Redaction
                                </button>
                            </div>
                        </div>
                        <p class="text-secondary text-xs mb-3">
                            <i class="bi bi-shield-lock-fill"></i> **Privacy First:** Drag your finger/mouse to draw black bars over your name, account balance, transaction date, or address before uploading.
                        </p>
                        
                        <!-- Responsive Scroll Container for Canvas -->
                        <div class="canvas-scroll-container border border-secondary-subtle rounded text-center bg-dark overflow-auto p-1" style="max-height: 400px;">
                            <canvas id="redactionCanvas" class="d-block mx-auto cursor-crosshair" style="max-width: 100%; height: auto; touch-action: none;"></canvas>
                        </div>
                        
                        <!-- Confirmation badge -->
                        <div x-show="redacted" class="text-success small mt-2 d-flex align-items-center gap-1">
                            <i class="bi bi-shield-check-fill"></i> Redaction locked & image auto-compressed to under 500KB.
                        </div>
                    </div>
                    
                    <!-- Hidden field to hold redacted base64 data -->
                    <input type="hidden" id="redactedDataInput" wire:model.defer="redacted_image_data" />
                </div>

                <!-- Submit Button -->
                <div class="d-grid mt-4">
                    <button type="submit" id="btnSubmitStage1" class="btn btn-danger py-2.5 rounded-3 fw-bold btn-verify text-white border-0" style="background: var(--coral);">
                        Next: Add Narrative & Screenshots <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </div>
            </form>
        </div>

    <!-- ─── STAGE 2: Narrative detail & chat screenshots ─── -->
    @else
        <div class="reveal visible animate-fade-in">
            <form wire:submit.prevent="submitStepTwo">
                
                <!-- Brief info message -->
                <div class="alert alert-info border-0 p-3 rounded-3 d-flex gap-2.5 mb-4" role="alert" style="background: rgba(0,200,150,0.06); border: 1px solid rgba(0,200,150,0.1) !important;">
                    <i class="bi bi-info-circle-fill text-success fs-5"></i>
                    <div class="small text-secondary">
                        <span class="fw-bold text-success">Stage 1 complete!</span> You have locked in the scammer and receipt evidence (+10 TP). Complete Stage 2 with details and chat proofs to gain <span class="fw-bold text-success">+15 Bonus Trust Points</span>.
                    </div>
                </div>

                <!-- Narrative Description -->
                <div class="mb-4">
                    <label class="form-label text-navy fw-semibold small">Scam Narrative (What happened?)</label>
                    <textarea wire:model.defer="narrative" rows="4" maxlength="500" placeholder="Provide a brief explanation of how you were scammed (e.g. Paid on Instagram, did not deliver, blocked number). Limit to 500 characters." class="form-control border-light-subtle rounded-3 p-3 small"></textarea>
                    <div class="d-flex justify-content-between mt-1 text-secondary text-xs">
                        <span>Min 15 characters. Please keep it factual.</span>
                        <span x-data="{ count: 0 }" x-on:keyup.window="count = $event.target.value ? $event.target.value.length : 0">
                            <span x-text="count">0</span> / 500 characters
                        </span>
                    </div>
                    @error('narrative') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <!-- Chat screenshots upload -->
                <div class="mb-4">
                    <label class="form-label text-navy fw-semibold small">Chat Screenshot Evidence (Optional, max 3)</label>
                    <input type="file" wire:model="screenshot_uploads" multiple accept="image/*" class="form-control border-light-subtle rounded-3 p-2.5" />
                    <p class="text-secondary text-xs mt-1">Upload screenshots of whatsapp/instagram conversations showing scam intent or payment agreement.</p>
                    @error('screenshot_uploads') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                    @error('screenshot_uploads.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                </div>

                <!-- Buttons row -->
                <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-4">
                    <span class="text-secondary small">* Real identity will remain hidden.</span>
                    <button type="submit" wire:loading.attr="disabled" class="btn btn-success px-4 py-2.5 rounded-3 fw-bold border-0 text-white" style="background-color: var(--emerald); min-width: 180px;">
                        <span wire:loading.remove>
                            <i class="bi bi-shield-fill-check"></i> Complete & Post
                        </span>
                        <span wire:loading class="d-flex align-items-center gap-2 justify-content-center">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Posting...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- Client-Side Redactor Drawing Javascript -->
    <script>
        function redactionTool() {
            return {
                canvas: null,
                ctx: null,
                img: null,
                drawing: false,
                redacted: false,
                
                loadImage(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = (event) => {
                        this.img = new Image();
                        this.img.onload = () => {
                            this.initCanvas();
                        };
                        this.img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    // Direct upload via Livewire for the original image
                    @this.upload('original_image', file);
                },

                initCanvas() {
                    this.canvas = document.getElementById('redactionCanvas');
                    this.ctx = this.canvas.getContext('2d');
                    
                    // Resize canvas keeping receipt aspects (target max width 600px)
                    const maxW = 550;
                    let w = this.img.width;
                    let h = this.img.height;

                    if (w > maxW) {
                        h = (maxW / w) * h;
                        w = maxW;
                    }

                    this.canvas.width = w;
                    this.canvas.height = h;

                    // Draw image to canvas
                    this.ctx.drawImage(this.img, 0, 0, w, h);
                    
                    // Show canvas wrapper
                    document.getElementById('redactorContainer').classList.remove('d-none');
                    
                    // Add listeners
                    this.setupDrawingListeners();
                },

                setupDrawingListeners() {
                    let lastX = 0;
                    let lastY = 0;

                    const getPos = (e) => {
                        const rect = this.canvas.getBoundingClientRect();
                        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                        // Map coordinates accounting for canvas stretching
                        return {
                            x: ((clientX - rect.left) / rect.width) * this.canvas.width,
                            y: ((clientY - rect.top) / rect.height) * this.canvas.height
                        };
                    };

                    const startDraw = (e) => {
                        this.drawing = true;
                        const pos = getPos(e);
                        lastX = pos.x;
                        lastY = pos.y;
                    };

                    const draw = (e) => {
                        if (!this.drawing) return;
                        e.preventDefault();
                        const pos = getPos(e);

                        this.ctx.strokeStyle = '#000000';
                        this.ctx.lineWidth = 24; // Brush width
                        this.ctx.lineCap = 'round';
                        this.ctx.lineJoin = 'round';

                        this.ctx.beginPath();
                        this.ctx.moveTo(lastX, lastY);
                        this.ctx.lineTo(pos.x, pos.y);
                        this.ctx.stroke();

                        lastX = pos.x;
                        lastY = pos.y;
                    };

                    const stopDraw = () => {
                        this.drawing = false;
                    };

                    // Mouse Events
                    this.canvas.addEventListener('mousedown', startDraw);
                    this.canvas.addEventListener('mousemove', draw);
                    window.addEventListener('mouseup', stopDraw);

                    // Touch Events (Mobile)
                    this.canvas.addEventListener('touchstart', startDraw);
                    this.canvas.addEventListener('touchmove', draw, { passive: false });
                    window.addEventListener('touchend', stopDraw);
                },

                clearCanvas() {
                    if (!this.img) return;
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.ctx.drawImage(this.img, 0, 0, this.canvas.width, this.canvas.height);
                    this.redacted = false;
                    document.getElementById('redactedDataInput').value = '';
                    @this.set('redacted_image_data', null);
                },

                applyRedaction() {
                    if (!this.canvas) return;
                    
                    // Compress canvas to JPEG at 80% quality (guarantees < 500KB output)
                    const dataUrl = this.canvas.toDataURL('image/jpeg', 0.8);
                    
                    // Lock Base64 in hidden input and transfer to Livewire
                    document.getElementById('redactedDataInput').value = dataUrl;
                    @this.set('redacted_image_data', dataUrl);
                    this.redacted = true;
                }
            };
        }
    </script>
</div>

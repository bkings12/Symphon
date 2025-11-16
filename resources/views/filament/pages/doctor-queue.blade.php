<x-filament-panels::page>
    <style>
        .queue-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .queue-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .queue-title {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }
        .queue-subtitle {
            font-size: 14px;
            color: #4b5563;
            margin-top: 4px;
        }
        .queue-refresh {
            font-size: 14px;
            color: #6b7280;
        }
        .empty-state {
            text-align: center;
            padding: 48px 0;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        .empty-icon {
            margin: 0 auto;
            height: 48px;
            width: 48px;
            color: #9ca3af;
        }
        .empty-title {
            margin-top: 8px;
            font-size: 14px;
            font-weight: 500;
            color: #111827;
        }
        .empty-text {
            margin-top: 4px;
            font-size: 14px;
            color: #6b7280;
        }
        .queue-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
        }
        @media (min-width: 1024px) {
            .queue-grid {
                grid-template-columns: 2fr 1fr;
            }
        }
        .queue-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .appointment-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            padding: 16px;
            cursor: pointer;
            transition: box-shadow 0.2s;
        }
        .appointment-card:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .appointment-card.selected {
            outline: 2px solid #f59e0b;
            outline-offset: 2px;
        }
        .appointment-card.in-progress {
            border-left: 4px solid #3b82f6;
        }
        .appointment-content {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
        }
        .appointment-main {
            flex: 1;
        }
        .appointment-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .queue-badge {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            background-color: #fef3c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .queue-badge-text {
            color: #d97706;
            font-weight: 600;
        }
        .appointment-info {
            flex: 1;
            min-width: 0;
        }
        .patient-name {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        .appointment-number {
            font-size: 14px;
            color: #4b5563;
            margin: 0;
        }
        .appointment-reason {
            margin-top: 8px;
            font-size: 14px;
            color: #374151;
        }
        .appointment-meta {
            margin-top: 12px;
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 12px;
            color: #6b7280;
        }
        .appointment-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-waiting {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-in-progress {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .btn-start {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #ffffff;
            background-color: #f59e0b;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-start:hover {
            background-color: #d97706;
        }
        .btn-start:focus {
            outline: 2px solid #f59e0b;
            outline-offset: 2px;
        }
        .sidebar {
            display: flex;
            flex-direction: column;
        }
        .sidebar-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            padding: 24px;
            position: sticky;
            top: 24px;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .sidebar-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        .btn-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }
        .btn-close:hover {
            color: #4b5563;
        }
        .close-icon {
            width: 20px;
            height: 20px;
        }
        .sidebar-content {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .detail-group {
            display: flex;
            flex-direction: column;
        }
        .detail-label {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .detail-value {
            font-size: 14px;
            color: #111827;
            margin: 0;
        }
        .detail-value-semibold {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .detail-value-red {
            font-size: 14px;
            font-weight: 500;
            color: #dc2626;
            margin: 0;
        }
        .sidebar-divider {
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }
        .btn-complete {
            width: 100%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #ffffff;
            background-color: #16a34a;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .btn-complete:hover {
            background-color: #15803d;
        }
        .btn-complete:focus {
            outline: 2px solid #16a34a;
            outline-offset: 2px;
        }
        .empty-sidebar {
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            padding: 24px;
            text-align: center;
        }
        .empty-sidebar-icon {
            margin: 0 auto;
            height: 48px;
            width: 48px;
            color: #9ca3af;
        }
        .empty-sidebar-text {
            margin-top: 8px;
            font-size: 14px;
            color: #6b7280;
        }
    </style>

    <div wire:poll.5s="loadAppointments" style="display: flex; flex-direction: column; gap: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 style="font-size: 24px; font-weight: 700; color: #111827; margin: 0;">Patient Queue</h2>
                <p style="font-size: 14px; color: #4b5563; margin-top: 4px;">
                    {{ count($appointments) }} patient(s) in queue
                </p>
            </div>
            <div style="font-size: 14px; color: #6b7280;">
                Auto-refreshing every 5 seconds
            </div>
        </div>

        @if(count($appointments) === 0)
            <div style="text-align: center; padding: 48px 0; background-color: #f9fafb; border-radius: 8px;">
                <svg style="margin: 0 auto; height: 48px; width: 48px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 style="margin-top: 8px; font-size: 14px; font-weight: 500; color: #111827;">No patients in queue</h3>
                <p style="margin-top: 4px; font-size: 14px; color: #6b7280;">The queue is currently empty.</p>
            </div>
        @else
            <div id="queue-grid" style="display: grid; grid-template-columns: 1fr; gap: 24px;">
                <!-- Queue List -->
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($appointments as $appointment)
                        <div 
                            style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; padding: 16px; cursor: pointer; transition: box-shadow 0.2s; @if($selectedAppointment && $selectedAppointment['id'] == $appointment['id']) outline: 2px solid #f59e0b; outline-offset: 2px; @endif @if($appointment['status'] == 'in_progress') border-left: 4px solid #3b82f6; @endif"
                            onmouseover="this.style.boxShadow='0 4px 6px -1px rgba(0, 0, 0, 0.1)'"
                            onmouseout="this.style.boxShadow='0 1px 2px 0 rgba(0, 0, 0, 0.05)'"
                            wire:click="viewAppointment({{ $appointment['id'] }})"
                        >
                            <div style="display: flex; align-items: flex-start; justify-content: space-between;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <div style="flex-shrink: 0; width: 40px; height: 40px; background-color: #fef3c7; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <span style="color: #d97706; font-weight: 600;">
                                                #{{ $appointment['queue_position'] }}
                                            </span>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">
                                                {{ $appointment['patient_name'] }}
                                            </h3>
                                            <p style="font-size: 14px; color: #4b5563; margin: 0;">
                                                {{ $appointment['appointment_number'] }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    @if($appointment['reason'])
                                        <p style="margin-top: 8px; font-size: 14px; color: #374151;">
                                            {{ Str::limit($appointment['reason'], 60) }}
                                        </p>
                                    @endif

                                    <div style="margin-top: 12px; display: flex; align-items: center; gap: 16px; font-size: 12px; color: #6b7280;">
                                        <span>📞 {{ $appointment['patient_phone'] }}</span>
                                        @if($appointment['checked_in_at'])
                                            <span>🕐 Checked in: {{ $appointment['checked_in_at'] }}</span>
                                        @endif
                                    </div>
                                </div>

                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                    <span style="display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 500; @if($appointment['status'] == 'waiting') background-color: #fef3c7; color: #92400e; @elseif($appointment['status'] == 'in_progress') background-color: #dbeafe; color: #1e40af; @endif">
                                        {{ ucfirst(str_replace('_', ' ', $appointment['status'])) }}
                                    </span>
                                    
                                    @if($appointment['status'] == 'waiting')
                                        <button
                                            wire:click.stop="startAppointment({{ $appointment['id'] }})"
                                            style="display: inline-flex; align-items: center; padding: 6px 12px; border: none; border-radius: 6px; font-size: 12px; font-weight: 500; color: #ffffff; background-color: #f59e0b; cursor: pointer; transition: background-color 0.2s;"
                                            onmouseover="this.style.backgroundColor='#d97706'"
                                            onmouseout="this.style.backgroundColor='#f59e0b'"
                                        >
                                            Start
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Appointment Details Sidebar -->
                <div>
                    @if($selectedAppointment)
                        <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb; padding: 24px; position: sticky; top: 24px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                                <h3 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Patient Details</h3>
                                <button
                                    wire:click="closeAppointmentView"
                                    style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; transition: color 0.2s;"
                                    onmouseover="this.style.color='#4b5563'"
                                    onmouseout="this.style.color='#9ca3af'"
                                >
                                    <svg style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                <div>
                                    <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Appointment Number</label>
                                    <p style="margin-top: 4px; font-size: 14px; font-weight: 600; color: #111827; margin: 0;">{{ $selectedAppointment['appointment_number'] }}</p>
                                </div>

                                <div>
                                    <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Patient Name</label>
                                    <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['patient_name'] }}</p>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Phone</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['patient_phone'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Gender</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ ucfirst($selectedAppointment['patient_gender'] ?? 'N/A') }}</p>
                                    </div>
                                </div>

                                @if($selectedAppointment['patient_email'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Email</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['patient_email'] }}</p>
                                    </div>
                                @endif

                                @if($selectedAppointment['patient_dob'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Date of Birth</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['patient_dob'] }}</p>
                                    </div>
                                @endif

                                @if($selectedAppointment['patient_address'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Address</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['patient_address'] }}</p>
                                    </div>
                                @endif

                                @if($selectedAppointment['reason'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Reason for Visit</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['reason'] }}</p>
                                    </div>
                                @endif

                                @if($selectedAppointment['patient_medical_history'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Medical History</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['patient_medical_history'] }}</p>
                                    </div>
                                @endif

                                @if($selectedAppointment['patient_allergies'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Allergies</label>
                                        <p style="margin-top: 4px; font-size: 14px; font-weight: 500; color: #dc2626; margin: 0;">{{ $selectedAppointment['patient_allergies'] }}</p>
                                    </div>
                                @endif

                                @if($selectedAppointment['notes'])
                                    <div>
                                        <label style="font-size: 12px; font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Notes</label>
                                        <p style="margin-top: 4px; font-size: 14px; color: #111827; margin: 0;">{{ $selectedAppointment['notes'] }}</p>
                                    </div>
                                @endif

                                <div style="padding-top: 16px; border-top: 1px solid #e5e7eb;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 12px;">
                                        <div>
                                            <label style="font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Queue Position</label>
                                            <p style="margin-top: 4px; font-size: 14px; font-weight: 600; color: #111827; margin: 0;">#{{ $selectedAppointment['queue_position'] }}</p>
                                        </div>
                                        <div>
                                            <label style="font-weight: 500; color: #6b7280; text-transform: uppercase; margin-bottom: 4px; display: block;">Status</label>
                                            <p style="margin-top: 4px; font-size: 14px; font-weight: 600; color: #111827; margin: 0;">
                                                {{ ucfirst(str_replace('_', ' ', $selectedAppointment['status'])) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($selectedAppointment['status'] == 'in_progress')
                                    <div style="padding-top: 16px;">
                                        <button
                                            wire:click="completeAppointment({{ $selectedAppointment['id'] }})"
                                            style="width: 100%; display: inline-flex; justify-content: center; align-items: center; padding: 8px 16px; border: none; border-radius: 6px; font-size: 14px; font-weight: 500; color: #ffffff; background-color: #16a34a; cursor: pointer; transition: background-color 0.2s;"
                                            onmouseover="this.style.backgroundColor='#15803d'"
                                            onmouseout="this.style.backgroundColor='#16a34a'"
                                        >
                                            Complete Appointment
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div style="background-color: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; padding: 24px; text-align: center;">
                            <svg style="margin: 0 auto; height: 48px; width: 48px; color: #9ca3af;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p style="margin-top: 8px; font-size: 14px; color: #6b7280;">Select a patient to view details</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <style>
        @media (min-width: 1024px) {
            #queue-grid {
                grid-template-columns: 2fr 1fr !important;
            }
        }
    </style>
    <script>
        // Apply responsive grid on load and resize
        function updateGrid() {
            const grid = document.getElementById('queue-grid');
            if (grid) {
                if (window.innerWidth >= 1024) {
                    grid.style.gridTemplateColumns = '2fr 1fr';
                } else {
                    grid.style.gridTemplateColumns = '1fr';
                }
            }
        }
        document.addEventListener('DOMContentLoaded', updateGrid);
        window.addEventListener('resize', updateGrid);
    </script>
</x-filament-panels::page>

<?php
use App\Http\Controllers\Helper;
use App\Helpers\PermissionHelper;
?>
 @foreach($students as $key => $student)
                                        @php
                                            $imageUrl = null;
                                            if ($student->student_photo) {
                                                $basePaths = [
                                                    public_path('uploads/studentPhotos'),
                                                    base_path('uploads/studentPhotos'),
                                                ];
                                                foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                                                    $filename = $student->student_photo . '.' . $ext;
                                                    foreach ($basePaths as $base) {
                                                        if (file_exists($base . '/' . $filename)) {
                                                            $imageUrl = asset('uploads/studentPhotos/' . $filename);
                                                            break 2;
                                                        }
                                                    }
                                                }
                                            }
                                            $initials = strtoupper(substr($student->firstname, 0, 1) . substr($student->lastname ?? '', 0, 1));
                                            $colors = ['#2f2ccb', '#059669', '#d97706', '#7c3aed', '#0891b2', '#dc2626'];
                                            $avatarColor = $colors[ord($student->firstname[0]) % count($colors)];

                                            $cardInfo = $student->card_info;
                                            $cardStatus = $cardInfo['status'] ?? null;
                                            $buttonType = $cardInfo['button_type'] ?? 'generate';
                                            $cardId = $cardInfo['card_id'] ?? null;
                                        @endphp
                                        <tr id="row-{{ $student->id }}">
                                            @if(PermissionHelper::canFeature('delete_student'))
                                                <td>
                                                    <input type="checkbox" class="student-select-checkbox"
                                                        value="{{ $student->id }}"
                                                        data-senior="{{ $senior }}" data-stream="{{ $stream }}">
                                                </td>
                                            @endif
                                            <td style="color:var(--t3);font-size:.8rem;font-weight:600;">
                                                {{ $students->firstItem() + $key }}
                                            </td>
                                            <td>
                                                @if($imageUrl)
                                                    <img src="{{ $imageUrl }}" alt="{{ $student->firstname }}" class="std-avatar">
                                                @else
                                                    <div class="std-avatar-placeholder"
                                                        style="background:{{ $avatarColor }}1a;color:{{ $avatarColor }};">{{ $initials }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td style="font-weight:600;">{{ $student->firstname }}</td>
                                            <td>{{ $student->lastname }}</td>
                                            <td><span
                                                    style="font-family:'DM Mono',monospace;font-size:.8rem;color:var(--t2);">{{ $student->admission_number ?? '—' }}</span>
                                            </td>
                                            <td>
                                                @if($student->gender === 'Male')
                                                    <span class="badge badge-blue"><i class="fas fa-mars" style="font-size:.65rem;"></i>
                                                        Male</span>
                                                @elseif($student->gender === 'Female')
                                                    <span class="badge badge-pink"><i class="fas fa-venus" style="font-size:.65rem;"></i>
                                                        Female</span>
                                                @else
                                                    <span class="badge badge-gray">{{ $student->gender ?? '—' }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($cardStatus === 'active')
                                                    <span class="badge" style="background: var(--gl); color: var(--g);">
                                                        <i class="fas fa-check-circle"></i> Active
                                                    </span>
                                                @elseif($cardStatus === 'revoked')
                                                    <span class="badge" style="background: var(--rl); color: var(--r);">
                                                        <i class="fas fa-ban"></i> Revoked
                                                    </span>
                                                @elseif($cardStatus === 'expired')
                                                    <span class="badge" style="background: var(--al); color: var(--a);">
                                                        <i class="fas fa-clock"></i> Expired
                                                    </span>
                                                @else
                                                    <span class="badge" style="background: #e2e8f0; color: #64748b;">
                                                        <i class="fas fa-id-card"></i> No Card
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display:flex;gap:.4rem;align-items:center;flex-wrap:wrap;">
                                                    <!-- View Student Details Button -->
                                                    @if(PermissionHelper::canFeature('view_student_details'))
                                                        <button class="btn-icon btn-view" onclick="viewStudent({{ $student->id }})"
                                                            title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif

                                                    <!-- ID Card Action Button based on status -->
                                                    @if(PermissionHelper::canModule('student_id_cards'))
                                                        @if($cardStatus === 'active')
                                                            @if(PermissionHelper::canFeature('view_cards'))
                                                                <button class="btn-icon" style="background: var(--g); color: #fff;"
                                                                    onclick="viewCard({{ $cardId }})" title="View ID Card">
                                                                    <i class="fas fa-id-card"></i>
                                                                </button>
                                                            @endif
                                                        @elseif($cardStatus === 'revoked')
                                                            @if(PermissionHelper::canFeature('reactivate_cards'))
                                                                <button class="btn-icon" style="background: var(--a); color: #fff;"
                                                                    onclick="reactivateCard({{ $cardId }}, '{{ addslashes($student->firstname) }} {{ addslashes($student->lastname) }}')"
                                                                    title="Reactivate Card">
                                                                    <i class="fas fa-sync-alt"></i>
                                                                </button>
                                                            @endif
                                                        @elseif($cardStatus === 'expired')
                                                            @if(PermissionHelper::canFeature('generate_cards'))
                                                                <button class="btn-icon btn-primary"
                                                                    onclick="generateSingleCard({{ $student->id }}, '{{ addslashes($student->firstname) }} {{ addslashes($student->lastname) }}')"
                                                                    title="Generate New Card">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            @endif
                                                        @else
                                                            @if(PermissionHelper::canFeature('generate_cards'))
                                                                <button class="btn-icon btn-primary"
                                                                    onclick="generateSingleCard({{ $student->id }}, '{{ addslashes($student->firstname) }} {{ addslashes($student->lastname) }}')"
                                                                    title="Generate ID Card">
                                                                    <i class="fas fa-magic"></i>
                                                                </button>
                                                            @endif
                                                        @endif
                                                    @endif

                                                    <!-- Edit & Delete buttons (existing) -->
                                                    @if(Helper::isAssignedClassTeacher($senior, $stream) || Helper::isTechSateAdminOrSchoolAdminsAlone())
                                                        @if(PermissionHelper::canFeature('edit_student'))
                                                            <button class="btn-icon btn-edit" onclick="editStudent({{ $student->id }})"
                                                                title="Edit Student">
                                                                <i class="fas fa-pen"></i>
                                                            </button>
                                                        @endif
                                                        @if(PermissionHelper::canFeature('delete_student'))
                                                            <button class="btn-icon btn-del"
                                                                onclick="deleteStudent({{ $student->id }}, '{{ addslashes($student->firstname) }} {{ addslashes($student->lastname) }}')"
                                                                title="Delete Student">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
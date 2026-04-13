<template>
    <aside
        :class="[
            'fixed pt-10 lg:pt-0 flex flex-col lg:mt-0 top-0 left-0 bg-white dark:bg-gray-900 dark:border-gray-800 text-gray-900 h-screen transition-all duration-300 ease-in-out z-50 border-r border-gray-200',
            {
                'lg:w-[250px]': isExpanded || isMobileOpen || isHovered,
                'lg:w-[115px]': !isExpanded && !isHovered,
                'translate-x-0 w-[250px]': isMobileOpen,
                '-translate-x-full': !isMobileOpen,
                'lg:translate-x-0': true,
            },
        ]"
        @mouseenter="!isExpanded && (isHovered = true)"
        @mouseleave="isHovered = false"
    >
        <div class="flex justify-center py-4">
            <Link href="/dashboard" v-if="!isMobileOpen">
                <div
                    class="flex gap-3 items-center"
                    v-if="isExpanded || isHovered || isMobileOpen"
                >
                    <div
                        class="flex justify-center items-center w-[45px] h-[45px] font-bold text-blue-600 bg-blue-50 rounded-xl"
                    >
                        SP
                    </div>
                    <div class="flex flex-col leading-tight">
                        <div class="font-bold tracking-wider text-blue-600">
                            Sistem
                        </div>
                        <div class="text-sm italic text-sky-500">
                            Penyimpanan
                        </div>
                    </div>
                </div>
                <div
                    v-else
                    class="flex justify-center items-center w-9 h-9 font-bold text-blue-600 bg-blue-50 rounded-xl"
                >
                    SP
                </div>
            </Link>
        </div>
        <div
            ref="sidebarScroll"
            data-simplebar
            class="flex overflow-y-auto flex-col px-4 pb-4 h-full duration-300 ease-linear"
        >
            <nav class="space-y-1">
                <div
                    :class="[
                        'flex flex-col',
                        !isExpanded && !isHovered
                            ? 'lg:items-center'
                            : 'items-start',
                    ]"
                    v-for="(menuGroup, groupIndex) in visibleMenuGroups"
                    :key="groupIndex"
                >
                    <h1
                        class="flex p-2 text-xs font-bold text-gray-500 uppercase"
                    >
                        {{ menuGroup.title }}
                    </h1>
                    <ul class="flex flex-col gap-2 w-full">
                        <li
                            v-for="(item, index) in menuGroup.items"
                            :key="item?.name || index"
                        >
                            <button
                                v-if="item.subItems && isItemVisible(item)"
                                @click="toggleSubmenu(groupIndex, index)"
                                :class="[
                                    'flex items-center w-full gap-2 p-2 font-medium rounded-lg group text-[14px]',
                                    {
                                        'bg-blue-50 text-indigo-500 dark:bg-indigo-500/[0.12] dark:text-blue-400':
                                            isSubmenuOpen(groupIndex, index),
                                        'text-gray-700 hover:bg-gray-100 group-hover:text-gray-700 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300':
                                            !isSubmenuOpen(groupIndex, index),
                                    },
                                    !isExpanded && !isHovered
                                        ? 'lg:justify-center'
                                        : 'lg:justify-start',
                                ]"
                                :data-active="
                                    isSubmenuOpen(groupIndex, index)
                                        ? 'true'
                                        : null
                                "
                            >
                                <span
                                    :class="[
                                        isSubmenuOpen(groupIndex, index)
                                            ? 'text-indigo-500 dark:text-blue-400'
                                            : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-300 dark:group-hover:text-gray-300',
                                    ]"
                                >
                                    <component
                                        :is="item.icon"
                                        class="w-5 h-5"
                                    />
                                </span>
                                <span
                                    v-if="
                                        isExpanded || isHovered || isMobileOpen
                                    "
                                    class="flex flex-1 gap-1.5 justify-between items-center min-w-0"
                                >
                                    <span class="truncate">{{
                                        item.name
                                    }}</span>
                                    <span
                                        v-if="
                                            item.name === 'Daftar Pengajuan' &&
                                            pendingCount > 0
                                        "
                                        class="inline-flex shrink-0 justify-center items-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-yellow-500 rounded-full"
                                    >
                                        {{ pendingCount }}
                                    </span>
                                    <span
                                        v-if="
                                            item.name === 'Transaksi Barang' &&
                                            goodsTransactionsPendingTotal > 0
                                        "
                                        class="inline-flex shrink-0 justify-center items-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-yellow-500 rounded-full"
                                    >
                                        {{ goodsTransactionsPendingTotal }}
                                    </span>
                                    <span
                                        v-if="
                                            item.name === 'Kelola Pengguna' &&
                                            pendingUsersCount > 0
                                        "
                                        class="inline-flex shrink-0 justify-center items-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold text-white bg-yellow-500 rounded-full"
                                    >
                                        {{ pendingUsersCount }}
                                    </span>
                                </span>
                                <ChevronDownIcon
                                    v-if="
                                        isExpanded || isHovered || isMobileOpen
                                    "
                                    :class="[
                                        'ml-auto w-5 h-5 transition-transform duration-200',
                                        {
                                            'rotate-180 text-indigo-500':
                                                isSubmenuOpen(
                                                    groupIndex,
                                                    index,
                                                ),
                                        },
                                    ]"
                                />
                            </button>
                            <Link
                                v-else-if="
                                    (item.path || item.pathName) &&
                                    (!item.permission || can(item.permission))
                                "
                                :href="
                                    item.pathName
                                        ? route(item.pathName)
                                        : item.path
                                "
                                :class="[
                                    'flex items-center w-full gap-2 p-2 font-medium rounded-lg group text-[14px]',
                                    {
                                        'bg-blue-100 text-indigo-500 dark:bg-indigo-500/[0.12] dark:text-blue-400':
                                            isActive(
                                                item.pathName
                                                    ? route(item.pathName)
                                                    : item.path,
                                            ),
                                        'text-gray-700 hover:bg-gray-100 group-hover:text-gray-700 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-gray-300':
                                            !isActive(
                                                item.pathName
                                                    ? route(item.pathName)
                                                    : item.path,
                                            ),
                                    },
                                    !isExpanded && !isHovered
                                        ? 'lg:justify-center'
                                        : 'lg:justify-start',
                                ]"
                                :data-active="
                                    isActive(
                                        item.pathName
                                            ? route(item.pathName)
                                            : item.path,
                                    )
                                        ? 'true'
                                        : null
                                "
                            >
                                <span
                                    :class="[
                                        isActive(
                                            item.pathName
                                                ? route(item.pathName)
                                                : item.path,
                                        ) || $page.url.startsWith(item.path)
                                            ? 'text-indigo-500 dark:text-blue-400'
                                            : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-300 dark:group-hover:text-gray-300',
                                    ]"
                                >
                                    <component
                                        :is="item.icon"
                                        class="w-4 h-4"
                                    />
                                </span>
                                <span
                                    v-if="
                                        isExpanded || isHovered || isMobileOpen
                                    "
                                    class="flex gap-1 items-center ml-0"
                                >
                                    <span>{{ item.name }}</span>
                                    <span
                                        v-if="
                                            item.pathName === 'presences.index' &&
                                            faceMismatchCount > 0
                                        "
                                        :class="[
                                            'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                            isActive(
                                                item.pathName
                                                    ? route(item.pathName)
                                                    : item.path,
                                            )
                                                ? 'bg-red-600 text-white'
                                                : 'bg-red-500 text-white',
                                        ]"
                                    >
                                        {{ faceMismatchCount }}
                                    </span>
                                </span>
                            </Link>
                            <transition
                                @enter="startTransition"
                                @after-enter="endTransition"
                                @before-leave="startTransition"
                                @after-leave="endTransition"
                            >
                                <div
                                    v-show="
                                        isSubmenuOpen(groupIndex, index) &&
                                        (isExpanded ||
                                            isHovered ||
                                            isMobileOpen)
                                    "
                                >
                                    <ul class="mt-2 ml-5 space-y-1">
                                        <template
                                            v-for="subItem in item.subItems ||
                                            []"
                                            :key="subItem.name"
                                        >
                                            <li
                                                v-if="
                                                    !subItem?.permission ||
                                                    can(subItem.permission)
                                                "
                                            >
                                                <Link
                                                    :href="
                                                        subItem.pathName
                                                            ? route(
                                                                  subItem.pathName,
                                                              )
                                                            : subItem.path
                                                    "
                                                    :class="[
                                                        'relative flex items-center gap-2 rounded-lg p-2 font-medium text-[14px]',
                                                        {
                                                            'bg-blue-50 text-indigo-500 dark:bg-indigo-500/[0.12] dark:text-blue-400':
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                ),
                                                            'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5':
                                                                !isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                ),
                                                        },
                                                    ]"
                                                    :data-active="
                                                        isActive(
                                                            subItem.pathName
                                                                ? route(
                                                                      subItem.pathName,
                                                                  )
                                                                : subItem.path,
                                                        )
                                                            ? 'true'
                                                            : null
                                                    "
                                                >
                                                    <span
                                                        :class="[
                                                            isSubmenuOpen(
                                                                groupIndex,
                                                                index,
                                                            )
                                                                ? 'text-indigo-500 dark:text-blue-400'
                                                                : 'text-gray-500 group-hover:text-gray-700 dark:text-gray-300 dark:group-hover:text-gray-300',
                                                        ]"
                                                    >
                                                    </span>
                                                    {{ subItem?.name }}
                                                    <span
                                                        class="flex gap-1 items-center ml-auto"
                                                    >
                                                        <!-- Badge for Pengajuan Sakit -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.sick.index' &&
                                                                getPendingCountByType(
                                                                    1,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    1,
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Pengajuan Cuti -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.leave.index' &&
                                                                getPendingCountByType(
                                                                    2,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    2,
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Pengajuan Khusus -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.others.index' &&
                                                                getPendingCountByType(
                                                                    3,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    3,
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Pengajuan Lembur -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.overtime.index' &&
                                                                getPendingCountByType(
                                                                    4,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    4,
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Pengajuan Piutang -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.debt.index' &&
                                                                getPendingCountByType(
                                                                    5,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    5,
                                                                )
                                                            }}
                                                        </span>

                                                        <!-- Badge for Pengajuan Reimbursement -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.reimbursement.index' &&
                                                                getPendingCountByType(
                                                                    6,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    6,
                                                                )
                                                            }}
                                                        </span>

                                                        <!-- Badge for Pengajuan Umum -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.general.index' &&
                                                                getPendingCountByType(
                                                                    7,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    7,
                                                                )
                                                            }}
                                                        </span>

                                                        <!-- Badge for Pengajuan Karyawan Harian -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'submission.employee.index' &&
                                                                getPendingCountByType(
                                                                    8,
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getPendingCountByType(
                                                                    8,
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Permintaan Barang -->
                                                        <span
                                                            v-if="
                                                                subItem.path ===
                                                                    '/material-requests' &&
                                                                getGoodsTransactionPendingCount(
                                                                    'material_requests',
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getGoodsTransactionPendingCount(
                                                                    "material_requests",
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Pengajuan Pembelian -->
                                                        <span
                                                            v-if="
                                                                subItem.path ===
                                                                    '/purchase-requests' &&
                                                                getGoodsTransactionPendingCount(
                                                                    'purchase_requests',
                                                                ) > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-600 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                getGoodsTransactionPendingCount(
                                                                    "purchase_requests",
                                                                )
                                                            }}
                                                        </span>
                                                        <!-- Badge for Data Pengguna (pending users) -->
                                                        <span
                                                            v-if="
                                                                subItem.path ===
                                                                    '/users' &&
                                                                pendingUsersCount >
                                                                    0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-yellow-500 text-white'
                                                                    : 'bg-yellow-500 text-white',
                                                            ]"
                                                        >
                                                            {{
                                                                pendingUsersCount
                                                            }}
                                                        </span>
                                                        <!-- Badge for Absensi Karyawan (face mismatch) -->
                                                        <span
                                                            v-if="
                                                                subItem.pathName ===
                                                                    'presences.index' &&
                                                                faceMismatchCount > 0
                                                            "
                                                            :class="[
                                                                'inline-flex min-w-[20px] h-5 shrink-0 justify-center items-center rounded-full px-1.5 text-[10px] font-bold',
                                                                isActive(
                                                                    subItem.pathName
                                                                        ? route(
                                                                              subItem.pathName,
                                                                          )
                                                                        : subItem.path,
                                                                )
                                                                    ? 'bg-red-600 text-white'
                                                                    : 'bg-red-500 text-white',
                                                            ]"
                                                        >
                                                            {{ faceMismatchCount }}
                                                        </span>
                                                        <span
                                                            v-if="subItem.new"
                                                            :class="[
                                                                'block rounded-full px-2.5 py-0.5 text-xs font-medium uppercase text-indigo-500 dark:text-blue-400',
                                                                {
                                                                    'bg-blue-100 dark:bg-indigo-500/20':
                                                                        isActive(
                                                                            subItem.pathName
                                                                                ? route(
                                                                                      subItem.pathName,
                                                                                  )
                                                                                : subItem.path,
                                                                        ),
                                                                    'bg-blue-50 group-hover:bg-blue-100 dark:bg-indigo-500/15 dark:group-hover:bg-indigo-500/20':
                                                                        !isActive(
                                                                            subItem.pathName
                                                                                ? route(
                                                                                      subItem.pathName,
                                                                                  )
                                                                                : subItem.path,
                                                                        ),
                                                                },
                                                            ]"
                                                        >
                                                            new
                                                        </span>
                                                        <span
                                                            v-if="subItem.pro"
                                                            :class="[
                                                                'block rounded-full px-2.5 py-0.5 text-xs font-medium uppercase text-indigo-500 dark:text-blue-400',
                                                                {
                                                                    'bg-blue-100 dark:bg-indigo-500/20':
                                                                        isActive(
                                                                            subItem.pathName
                                                                                ? route(
                                                                                      subItem.pathName,
                                                                                  )
                                                                                : subItem.path,
                                                                        ),
                                                                    'bg-blue-50 group-hover:bg-blue-100 dark:bg-indigo-500/15 dark:group-hover:bg-indigo-500/20':
                                                                        !isActive(
                                                                            subItem.pathName
                                                                                ? route(
                                                                                      subItem.pathName,
                                                                                  )
                                                                                : subItem.path,
                                                                        ),
                                                                },
                                                            ]"
                                                        >
                                                            pro
                                                        </span>
                                                    </span>
                                                </Link>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </transition>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </aside>
</template>

<script setup>
import {
    GridIcon,
    ChecklistIcon,
    NotificationStatusIcon,
    AddressBookIcon,
    ClockIcon,
    CalendarIcon,
    UserCard,
    FormChecklistIcon,
    VehicleIcon,
    FormIcon,
    MoneyIcon,
    QuestionnaireIcon,
    CartIcon,
    ArrowCircleIcon,
    ParcelIcon,
    ChartIcon,
    DeliveryIcon,
    EditIcon,
    HomeIcon,
    OfficeIcon,
    BriefCase,
    TrackingIcon,
    SettingIcon,
    CubeIcon,
    UserGroupIcon,
    RootIcon,
    GearIcon,
    UserCircleIcon,
    ChatIcon,
    MailIcon,
    DocsIcon,
    PieChartIcon,
    ChevronDownIcon,
    PageIcon,
    TableIcon,
    ListIcon,
    PlugInIcon,
} from "@/Components/icons";
import BoxCubeIcon from "@/Components/icons/BoxCubeIcon.vue";
import { useSidebar } from "@/Composables/useSidebar";
import BarChartIcon from "../icons/BarChartIcon.vue";
import { computed, ref, onMounted, onBeforeUnmount, nextTick } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useAuth } from "@/Composables/useAuth";

const { user, is, can } = useAuth();
const page = usePage();

const sidebarCounts = computed(
    () => page.props.sidebarCounts || { total: 0, on_progress: 0 },
);

// Pending submissions count
const pendingSubmissions = computed(() => {
    const data = page.props.pendingSubmissionsCount || {
        total: 0,
        by_type: {},
    };
    console.log("=== PENDING SUBMISSIONS DATA ===", data);
    return data;
});
const pendingCount = computed(() => pendingSubmissions.value.total || 0);

// Pending users verification count
const pendingUsersCount = computed(() => page.props.pendingUsersCount || 0);

// Goods transactions pending count
const goodsTransactionsPending = computed(() => {
    const data = page.props.goodsTransactionsPendingCount || {
        total: 0,
        by_type: {},
    };
    return data;
});
const goodsTransactionsPendingTotal = computed(
    () => goodsTransactionsPending.value.total || 0,
);

// Face mismatch count – attendances today where user flagged face as wrong
const faceMismatchCount = computed(() => page.props.faceMismatchCount || 0);

// Helper function to get pending count by transaction type
function getGoodsTransactionPendingCount(type) {
    return goodsTransactionsPending.value.by_type?.[type] || 0;
}

// Helper function to get pending count by submission type
function getPendingCountByType(type) {
    const count = pendingSubmissions.value.by_type?.[type] || 0;
    console.log(
        `getPendingCountByType(${type}) = ${count}`,
        pendingSubmissions.value.by_type,
    );
    return count;
}

// Auto-scroll to active menu
const sidebarScroll = ref(null);

function getScrollElement() {
    const root = sidebarScroll.value;
    if (!root) return null;
    const simplebar = root.querySelector?.(".simplebar-content-wrapper");
    return simplebar || root;
}

function scrollActiveIntoView() {
    const container = getScrollElement();
    if (!container) return;
    const activeEl = container.querySelector('[data-active="true"]');
    if (!activeEl) return;

    const margin = 12; // px threshold to avoid tiny jumps
    const cRect = container.getBoundingClientRect();
    const eRect = activeEl.getBoundingClientRect();

    const above = eRect.top < cRect.top + margin;
    const below = eRect.bottom > cRect.bottom - margin;

    if (!above && !below) return; // Already sufficiently visible

    let newTop = container.scrollTop;
    if (above) {
        newTop -= cRect.top + margin - eRect.top;
    } else if (below) {
        newTop += eRect.bottom - (cRect.bottom - margin);
    }

    if (typeof container.scrollTo === "function") {
        container.scrollTo({ top: newTop, behavior: "smooth" });
    } else {
        container.scrollTop = newTop;
    }
}

function onInertiaFinish() {
    nextTick(() => scrollActiveIntoView());
}

onMounted(() => {
    nextTick(() => scrollActiveIntoView());
    window.addEventListener("inertia:finish", onInertiaFinish);
});

onBeforeUnmount(() => {
    window.removeEventListener("inertia:finish", onInertiaFinish);
});

const { isExpanded, isMobileOpen, isHovered, openSubmenu } = useSidebar();

// Helper function to check if item with subItems has any visible subItems
function hasVisibleSubItems(item) {
    if (
        !item ||
        !item.subItems ||
        !Array.isArray(item.subItems) ||
        item.subItems.length === 0
    ) {
        return false;
    }
    // Cek apakah ada minimal satu subItem yang visible (memiliki permission atau tidak ada permission)
    const hasVisible = item.subItems.some((sub) => {
        if (!sub) return false;
        // Jika subItem tidak punya permission, maka visible
        // Jika subItem punya permission, cek apakah user bisa akses
        return !sub.permission || can(sub.permission);
    });
    return hasVisible;
}

// Helper function to check if an item should be visible
function isItemVisible(item) {
    // Cek jika item tidak ada
    if (!item) {
        return false;
    }

    // Item dengan submenu: cek apakah ada subItem yang visible
    if (
        item.subItems &&
        Array.isArray(item.subItems) &&
        item.subItems.length > 0
    ) {
        // Parent menu hanya visible jika ada minimal satu subItem yang visible
        const hasVisibleChildren = hasVisibleSubItems(item);

        console.log("=== HAS VISIBLE CHILDREN ===", hasVisibleChildren);
        console.log("=== ITEM ===", item);
        console.log("=== ITEM PERMISSION ===", item.permission);
        console.log("=== CAN PERMISSION ===", can(item.permission));
        console.log("=== CAN ===", can);
        // Jika tidak ada child yang visible, parent tidak visible
        if (!hasVisibleChildren) {
            return false;
        }

        // Jika ada child yang visible, cek permission parent jika ada
        if (item.permission) {
            // Jika parent punya permission, harus bisa akses parent
            return can(item.permission);
        }

        // Jika parent tidak punya permission dan ada child yang visible, parent visible
        return true;
    }

    // Item biasa: cek permission langsung
    return !item.permission || can(item.permission);
}

function hasVisibleItems(group) {
    if (!group || !Array.isArray(group.items)) return false;

    return group.items.some((item) => isItemVisible(item));
}

const menuGroups = [
    {
        title: "Menu",
        items: [
            {
                icon: GridIcon,
                name: "Dashboard",
                path: "/dashboard",
                permission: "dashboard.view",
            },
        ],
    },
    {
        title: "Penyimpanan",
        items: [
            {
                icon: MoneyIcon,
                name: "Transaksi Barang",
                subItems: [
                    {
                        name: "Permintaan Barang",
                        path: "/material-requests",
                        pro: false,
                        permission: "material_requests.view",
                    },
                    {
                        name: "Pengajuan Pembelian",
                        path: "/purchase-requests",
                        pro: false,
                        permission: "purchase_requests.view",
                    },
                    {
                        name: "Pembelian Barang",
                        path: "/purchase-orders",
                        pro: false,
                        permission: "purchase_orders.view",
                    },
                    {
                        name: "Perpindahan Barang",
                        path: "/good-transfers",
                        pro: false,
                        permission: "good_transfers.view",
                    },
                    {
                        name: "Penerimaan Barang",
                        path: "/good-receipts",
                        pro: false,
                        permission: "good_receipts.view",
                    },
                    {
                        name: "Pemakaian Barang",
                        path: "/good-issues",
                        pro: false,
                        permission: "good_issues.view",
                    },
                ],
            },
            {
                icon: ParcelIcon,
                name: "Daftar barang",
                pathName: "items.index",
                permission: "items.view",
            },
            {
                icon: ChartIcon,
                name: "Stok Masuk",
                pathName: "stock-in.index",
                pro: false,
                permission: "stock_in.view",
            },
            {
                icon: DeliveryIcon,
                name: "Stok Keluar",
                pathName: "stock-out.index",
                pro: false,
                permission: "stock_out.view",
            },
            // {
            //     icon: EditIcon,
            //     name: "Penyesuaian Stok",
            //     path: "/stock",
            // },
            {
                icon: FormChecklistIcon,
                name: "Rekap Stok",
                pathName: "stock-recap.index",
                permission: "stock_recap.view",
            },
        ],
    },
    {
        title: "Konfigurasi",
        items: [
            {
                icon: HomeIcon,
                name: "Daftar Cabang",
                pathName: "branches.index",
                permission: "branches.view",
            },
            {
                icon: OfficeIcon,
                name: "Daftar Departemen",
                pathName: "departments.index",
                permission: "departments.view",
            },

            {
                icon: UserGroupIcon,
                name: "Kelola Pengguna",
                subItems: [
                    {
                        name: "Data Pengguna",
                        path: "/users",
                        permission: "users.view",
                    },
                    {
                        name: "Jabatan & Hak Akses",
                        path: "/roles",
                        permission: "roles.view",
                    },
                ],
            },
            {
                icon: TrackingIcon,
                name: "Kelola Barang",
                subItems: [
                    {
                        icon: GridIcon,
                        name: "Kategori Barang",
                        pathName: "item-categories.index",
                        permission: "item_categories.view",
                    },
                    {
                        icon: SettingIcon,
                        name: "Satuan Barang",
                        pathName: "units.index",
                        permission: "units.view",
                    },
                ],
            },
        ],
    },
];

// Computed property untuk memfilter menuGroups yang visible
const visibleMenuGroups = computed(() => {
    return menuGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => isItemVisible(item)),
        }))
        .filter((group) => group.items.length > 0);
});

const isActive = (maybeUrl) => {
    if (!maybeUrl) return false;
    try {
        const url = new URL(maybeUrl, window.location.origin);
        const current = new URL(page.url, window.location.origin);
        return (
            current.pathname === url.pathname ||
            current.pathname.startsWith(url.pathname + "/")
        );
    } catch (e) {
        return page.url.startsWith(maybeUrl);
    }
};

const toggleSubmenu = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    openSubmenu.value = openSubmenu.value === key ? null : key;
};

const isAnySubmenuRouteActive = computed(() => {
    return menuGroups.some((group) =>
        group.items.some(
            (item) =>
                item.subItems &&
                item.subItems.some((subItem) => isActive(subItem.path)),
        ),
    );
});

const isSubmenuOpen = (groupIndex, itemIndex) => {
    const key = `${groupIndex}-${itemIndex}`;
    return (
        openSubmenu.value === key ||
        (isAnySubmenuRouteActive.value &&
            menuGroups[groupIndex].items[itemIndex].subItems?.some((subItem) =>
                isActive(subItem.path),
            ))
    );
};

const startTransition = (el) => {
    el.style.height = "auto";
    const height = el.scrollHeight;
    el.style.height = "0px";
    el.offsetHeight; // force reflow
    el.style.height = height + "px";
};

const endTransition = (el) => {
    el.style.height = "";
};
</script>

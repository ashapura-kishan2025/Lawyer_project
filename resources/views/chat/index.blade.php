@php
    //custom
    $customizerHidden = 'customizer-hide';
@endphp

@extends('layouts.horizontalLayout')
@section('title', 'Chat - Apps')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.scss')
@endsection

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-chat.scss')
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/app-chat.js')
    <script>
        var receiver_user_id;
        $("#app-chat-conversation").removeClass("d-none");
        $(".app-chat-history").addClass("d-none");
        $(document).ready(function() {
            // On Click event
            $(".chat-contact-list-item").on("click", function() {

                $("#app-chat-conversation").addClass("d-none")
                $(".app-chat-history").removeClass("d-none");
                receiver_user_id = $(this).data('user-id');
                fetchChatHistory(receiver_user_id);
                $("#input_receiver_id").val(receiver_user_id);
                // Get Contact list personal data in chat contact header start
                $.ajax({
                    url: "/chat/receiver/" + receiver_user_id, // Using dynamic receiver ID in URL
                    type: "GET",
                    success: function(response) {
                        var receiver_name = response.user.name;
                        $("#receiver-name").text(receiver_name);
                        var role = response.role;
                        if (typeof role === "undefined") {
                            $("#user-role").text('');
                        } else {
                            $("#user-role").text(role);
                        }
                        $("#right-avatar").html("");
                        $("#right-avatar").append(`
                          <div class="flex-shrink-0  rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                            <span class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100">
                                ${response.usernameimg}
                            </span>
                          </div>`);
                        scrollToBottom(); // Scroll to the latest message
                    },
                    error: function(xhr, status, error) {
                        console.log("Error:", error);
                    }
                });
                // Get Contact list personal data in chat contact header end

                // Reload messages every 7 seconds
                setInterval(() => fetchChatHistory(receiver_user_id), 7000);
            });
            // On Click event
        });


        function fetchChatHistory(receiver_user_id) {
            // AJAX for fetch Chat History START
            $.ajax({
                url: "/chat/" + receiver_user_id, // Using dynamic receiver ID in URL
                type: "GET",
                success: function(response) {
                    if (response.messages.length == 0) {
                        $(".chat-history").html("");
                        $("#no-chat-history").removeClass("d-none");
                        $("#no-chat-history span").removeClass("d-none").addClass("position-fixed");
                        // setTimeout(() => {
                        //     $(".chat-history").scrollTop(0);
                        // }, 10);
                        return false;
                    } else {
                        $(".chat-history").html("");
                        $("#no-chat-history").addClass("d-none");
                        $("#no-chat-history span").addClass("d-none");
                    }


                    let loggedInUserID = response.loggedInUserID;

                    response.messages.forEach(message => {
                        // console.log("loggedInUserID : " + loggedInUserID);
                        // console.log(" message.sender_id " + message.sender_id);
                        // return false;
                        let messageClass = (message.sender_id ==
                                loggedInUserID) ? "chat-message-right" :
                            "";

                        if (messageClass == "chat-message-right") {
                            timePos = "text-end";
                        } else {
                            timePos = "";
                        }

                        let message_time = message.created_at;

                        // Convert timestamp to Date object
                        let date = new Date(message_time);

                        // Extract hours, minutes, and determine AM/PM
                        let hours = date.getHours();
                        let minutes = date.getMinutes().toString().padStart(2, "0");
                        let amPm = hours >= 12 ? "PM" : "AM";

                        // Convert 24-hour format to 12-hour format
                        hours = hours % 12 || 12; // Convert 0 to 12 for 12 AM case

                        let messageHTML = `
                            <li class="chat-message  mb-4 ${messageClass}">
                              <div class="d-flex overflow-hidden">
                                  <div class="chat-message-wrapper flex-grow-1">
                                      <div class="chat-message-text">
                                          ${message.message}
                                      </div>
                                      <div class="${timePos} text-muted mt-1">
                                           <small>${hours}:${minutes} ${amPm}</small>
                                      </div>
                                  </div>
                                  <div class="user-avatar flex-shrink-0 ms-4">
                                      {{-- <div class="avatar avatar-sm">
                                          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar"
                                              class="rounded-circle">
                                      </div> --}}
                                  </div>
                              </div>
                            </li>
                          `;

                        $(".chat-history").append(messageHTML);
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Error:", error);
                }
            });
            // AJAX for fetch Chat History END
        }

        function scrollToBottom() {
            let chatHistory = document.querySelector(".chat-history-body");
            if (chatHistory) {
                chatHistory.scrollTop = chatHistory.scrollHeight;
            }
        }



        //  (".chat-search-input").on("keyup", function() {
        //     alert(hi);
        //     let sidebar = document.querySelector("#chat-list");
        //     if (sidebar) {
        //         alert(sidebar);
        //         sidebar.scrollTop = 0; // Reset scroll to top
        //     }
        // });
    </script>
@endsection

@section('content')
    <div class="chat-messages"></div>
    <div class="app-chat card overflow-hidden">
        <div class="row g-0">
            <!-- Chat & Contacts -->
            <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
                <div class="sidebar-header h-px-75 px-5 border-bottom d-flex align-items-center">
                    <div class="d-flex align-items-center me-6 me-lg-0 w-100">
                        <div class="flex-grow-1 input-group input-group-merge">
                            <span class="input-group-text" id="basic-addon-search31"><i class="ti ti-search"></i></span>
                            <input type="text" class="form-control chat-search-input" placeholder="Search..."
                                aria-label="Search..." aria-describedby="basic-addon-search31">
                        </div>
                    </div>
                    <i class="ti ti-x ti-lg cursor-pointer position-absolute top-50 end-0 translate-middle d-lg-none d-block"
                        data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
                </div>
                <div class="sidebar-body">
                    <!-- Chats -->
                    <ul class="list-unstyled chat-contact-list  py-2 mb-0" id="chat-list">
                        <li class="chat-contact-list-item-title mt-0">
                            <h5 class="text-primary mb-0">Chats</h5>
                        </li>
                        <li class="chat-contact-list-item chat-list-item-0 d-none">
                            <h6 class="text-muted mb-0">No Chats Found</h6>
                        </li>
                        @foreach ($users as $user)
                            <li class="chat-contact-list-item mb-1" id="chat-contact-list-item-{{ $user->id }}"
                                data-user-id="{{ $user->id }}">
                                <a class="d-flex align-items-center">
                                    @php
                                        $name = explode(' ', $user->name);
                                        $initials = strtoupper($name[0][0] . (isset($name[1]) ? $name[1][0] : ''));
                                    @endphp
                                    <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                                        style="width: 40px; height: 40px; font-size: 16px; font-weight: bold;">
                                        <span
                                            class="rounded-circle d-flex align-items-center justify-content-center w-100 h-100">
                                            {{ $initials }}
                                        </span>
                                    </div>
                                    <div class="chat-contact-info flex-grow-1 ms-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="chat-contact-name text-truncate m-0 fw-normal">{{ $user->name }}
                                            </h6>
                                            {{-- <small class="text-muted">5 Minutes</small> --}}
                                        </div>
                                        <small class="chat-contact-status text-truncate">{{ $user->role_name }}</small>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <!-- /Chat contacts -->

            {{-- At first it opens this  --}}
            <div class="col app-chat-conversation d-flex align-items-center justify-content-center flex-column"
                id="app-chat-conversation" style="height: calc(100vh - 10.9rem);">

                <div class="bg-label-primary p-8 rounded-circle">
                    <i class="fa fa-message fs-1"></i>
                </div>
                <p class="my-4">Select a contact to start a conversation.</p>
                <button class="btn btn-primary app-chat-conversation-btn waves-effect waves-light d-lg-none mb-5"
                    id="app-chat-conversation-btn" data-bs-toggle="sidebar" data-overlay
                    data-target="#app-chat-contacts">Select Contact</button>
            </div>
            {{-- At first it opens this --}}
            <!-- Chat History -->
            <div class="col app-chat-history">
                <div class="chat-history-wrapper">
                    <div class="chat-history-header border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex overflow-hidden align-items-center">
                                <i class="ti ti-menu-2 ti-lg cursor-pointer d-lg-none d-block me-4" data-bs-toggle="sidebar"
                                    data-overlay data-target="#app-chat-contacts"></i>
                                <div class="flex-shrink-0 " id="right-avatar">

                                </div>
                                <div class="chat-contact-info flex-grow-1 ms-4">
                                    <h6 class="m-0 fw-normal" id="receiver-name"></h6>
                                    <small class="user-status text-body" id="user-role"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="chat-history-body">
                        <div id="no-chat-history" class="d-none d-flex  align-items-center justify-content-center mb-auto">
                            <span class="">No greetings, let's get started.</span>
                        </div>
                        <ul class="list-unstyled chat-history">
                        </ul>
                    </div>
                    <!-- Chat message form -->
                    <div class="chat-history-footer shadow-xs">
                        <form action="{{ route('chat.sendMessage') }}" method="POST"
                            class="form-send-message d-flex justify-content-between align-items-center">
                            @csrf
                            <input id="input_receiver_id" type="hidden" name="receiver_id">
                            <input id="input_message" class="form-control message-input border-0 me-4 shadow-none"
                                placeholder="Type your message here..." name="message" autocomplete="off">
                            <div class="message-actions d-flex align-items-center">
                                <button class="btn btn-primary d-flex send-msg-btn">
                                    <span class="align-middle d-md-inline-block d-none">Send</span>
                                    <i class="ti ti-send ti-16px ms-md-2 ms-0"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Chat History -->



            <div class="app-overlay"></div>
        </div>
    </div>
@endsection

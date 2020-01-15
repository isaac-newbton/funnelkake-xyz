import React, { useState, useEffect } from 'react';
import ReactDOM from 'react-dom';
import TicketUserList from './TicketUserList';

const AssignedTicketUsers = props => {
	// TODO: getting this tickets id from react is really ugly and unreliable
	const ticketId = document.getElementById('single-ticket').dataset.ticketid; 

	const [staffUsers, setStaffUsers] = useState([])
	const [organizationUsers, setOrganizationUsers] = useState([])
	const [activeUsers, setActiveUsers] = useState([])

	async function fetchData() {
		await fetch(window.location.protocol + '//' + window.location.host + '/api/ticket/organization/users/'+ticketId)
			.then((response) => {
				return response.json();
			})
			.then((userData) => {
				setOrganizationUsers(userData)
			})

		await fetch(window.location.protocol + '//' + window.location.host + '/api/ticket/staff/users/'+ticketId)
			.then((response) => {
				return response.json();
			})
			.then((userData) => {
				setStaffUsers(userData)
			})
		await fetch(window.location.protocol + '//' + window.location.host + '/api/ticket/users/'+ticketId)
			.then((response) => {
				return response.json();
			})
			.then((userData) => {
				setActiveUsers(userData)
			})
		.catch(error => console.log('TODO: write nice error message'))
	}

	function addUserHandler(user) {
		fetch(window.location.protocol + '//' + window.location.host + '/api/ticket/organization/users/add/'+ticketId, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept' : 'application/json',
			},
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			referrerPolicy: 'no-referrer',
			body: JSON.stringify(user)
		})
		.then((response) => response.json())
		.then((data) => {
			console.log(data);
		})
		fetchData();
	}

	function removeUserHandler(user) {
		fetch(window.location.protocol + '//' + window.location.host + '/api/ticket/organization/users/remove/'+ticketId, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept' : 'application/json',
			},
			mode: 'cors', // no-cors, *cors, same-origin
			cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
			credentials: 'same-origin', // include, *same-origin, omit
			referrerPolicy: 'no-referrer',
			body: JSON.stringify(user)
		})
		.then((response) => response.json())
		.then((data) => {
			console.log(data);
		})
		fetchData();
	}
	
	const activeUserHandler = (user) => {
		// 	// TODO: Its not removing users or adding ?
		if (activeUsers.find(activeUser => activeUser.id === user.id)) {
			removeUserHandler(user)
			setActiveUsers(activeUsers.filter(activeUser => activeUser.id !== user.id));
		} else {
			addUserHandler(user)
			setActiveUsers([...activeUsers, user])
		}
	}
	
	useEffect(() => {
		{console.log('fetching data...')}
		fetchData();
		{console.log('fetching data completed')}
		// updateData(activeUsers)
	}, [activeUsers.length])


	return (
		<div>
			<h5 className="py-2">Staff Users</h5>
			<TicketUserList activeUsers={activeUsers} activeUserHandler={activeUserHandler} users={staffUsers} />
			<h5 className="py-2">Organization Users</h5>
			<TicketUserList activeUsers={activeUsers} activeUserHandler={activeUserHandler} users={organizationUsers} />
		</div>
	)
}

ReactDOM.render(<AssignedTicketUsers />, document.getElementById('assigned-ticket-users'))
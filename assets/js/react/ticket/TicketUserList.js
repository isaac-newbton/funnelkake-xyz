import React, { useEffect } from 'react';
import TicketUser from './TicketUser';
import ButtonGroup from 'react-bootstrap/ButtonGroup';

const TicketUserList = props => {
		return (
		<>
		<h5 className="py-2">{props.title}</h5>
		<ButtonGroup vertical>
			{props.users.map( user => {
				// TODO: its not giving the correct status
				return <TicketUser
				status={props.activeUsers.find( activeUser => activeUser.id === user.id) != null}
				key={user.id}
				user={user}
				activeUserHandler={props.activeUserHandler}
				/>
			})}
		</ButtonGroup>
		</>
	)
}

export default TicketUserList;
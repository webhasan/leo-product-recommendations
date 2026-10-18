/**
 * Text input that reports changes after the user stops typing.
 *
 * Drop-in replacement for the `react-debounce-input` package (React 15-18 only):
 * - onChange( { target: { value } } ) fires `debounceTimeout` ms after the last keystroke,
 *   when the value has at least `minLength` characters;
 * - going below `minLength` after a search reports an empty value right away.
 */
const { useState, useRef, useEffect } = wp.element;

const DebounceInput = ( { minLength = 0, debounceTimeout = 300, onChange, ...props } ) => {
	const [ value, setValue ] = useState( '' );
	const timer = useRef( null );
	const lastNotified = useRef( '' );

	useEffect( () => () => clearTimeout( timer.current ), [] );

	const notify = ( nextValue ) => {
		if ( nextValue === lastNotified.current ) {
			return;
		}
		lastNotified.current = nextValue;
		onChange( { target: { value: nextValue } } );
	};

	const handleChange = ( event ) => {
		const nextValue = event.target.value;
		setValue( nextValue );
		clearTimeout( timer.current );

		if ( nextValue.length >= minLength ) {
			timer.current = setTimeout( () => notify( nextValue ), debounceTimeout );
		} else if ( lastNotified.current !== '' ) {
			notify( '' );
		}
	};

	return <input type="text" { ...props } value={ value } onChange={ handleChange } />;
};

export default DebounceInput;

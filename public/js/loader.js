class Loader
{
    loaderElement;
    capacity;
    step;
    currentSteps;

    constructor (capacity, step)
    {
        this.loaderElement = $('.loader');
        this.capacity = capacity;
        this.step = step;
        this.currentSteps = 0;

        this.reset = this.reset.bind (this);
        this.stepFactor = this.stepFactor.bind (this);
        this.takeStep = this.takeStep.bind (this);

        this.reset ();
    }

    reset ()
    {
        this.currentSteps = 0;
        this.loaderElement.width (0);
    }

    stepFactor ()
    {
        return (100 * this.step) / this.capacity;
    }

    takeStep ()
    {
        this.currentSteps++;
        this.loaderElement.width (`${this.currentSteps * this.stepFactor()}%`);

        if (this.currentSteps == this.capacity)
            setTimeout (this.reset, 1000);    
    }
}

export default Loader;